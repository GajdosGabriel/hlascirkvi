<?php

namespace App\Console\Commands;

use App\Models\Canal;
use App\Models\Post;
use App\Services\Youtube\YoutubeApi;
use Illuminate\Console\Command;

/**
 * Príspevky uložené pod nesprávnym kanálom — napr. organizácie/1 (Gabriel
 * Gajdoš) mal 298 videí, ktoré si niekto len prezeral prihlásený a import
 * ich priradil jeho kanálu namiesto kanála, ktorému skutočne patria.
 *
 * Skutočného vlastníka zistí YouTube API (snippet.channelId) a porovná ho
 * s `canals.youtube_channel`. Vie presunúť len video, ktorého kanál už na
 * webe existuje — chýbajúci kanál treba založiť ručne (youtube:channels).
 */
class YoutubeFixCanalMismatch extends Command
{
    protected $signature = 'youtube:fix-canal-mismatch
        {canal : ID kanála, ktorého príspevky sa majú overiť}
        {--fix : Zapísať nájdené presuny (bez prepínača len vypíše návrh)}';

    protected $description = 'Presunie príspevky kanála na kanál, ktorému skutočne patria podľa YouTube';

    public function handle(YoutubeApi $api): int
    {
        $canal = Canal::withTrashed()->findOrFail((int) $this->argument('canal'));

        $posts = Post::withTrashed()
            ->where('canal_id', $canal->id)
            ->whereNotNull('video_id')
            ->get(['id', 'video_id', 'title']);

        if ($posts->isEmpty()) {
            $this->info("Kanál #{$canal->id} {$canal->title} nemá príspevky s video_id.");

            return self::SUCCESS;
        }

        $channelIdByVideo = [];

        foreach ($posts->pluck('video_id')->unique()->chunk(YoutubeApi::BATCH) as $chunk) {
            foreach ($api->videos($chunk->all(), 'snippet') as $id => $video) {
                $channelIdByVideo[$id] = $video->snippet->channelId ?? null;
            }
        }

        $canalsByYoutubeId = Canal::withTrashed()
            ->whereNotNull('youtube_channel')
            ->where('youtube_channel', '<>', '')
            ->get(['id', 'title', 'youtube_channel'])
            ->keyBy('youtube_channel');

        $rows = [];
        $moved = 0;
        $notFoundOnYoutube = 0;
        $noCanalForChannel = 0;

        foreach ($posts as $post) {
            $channelId = $channelIdByVideo[$post->video_id] ?? null;

            if ($channelId === null) {
                $notFoundOnYoutube++;
                continue;
            }

            if ($channelId === $canal->youtube_channel) {
                continue;
            }

            $target = $canalsByYoutubeId->get($channelId);

            if ($target === null) {
                $noCanalForChannel++;
                continue;
            }

            $rows[] = [$post->id, $post->title, $target->id, $target->title];

            if ($this->option('fix')) {
                Post::withTrashed()->whereKey($post->id)->update(['canal_id' => $target->id]);
            }

            $moved++;
        }

        if ($rows === []) {
            $this->info('Niet čo presúvať.');
        } else {
            $this->table(['post', 'názov', 'cieľový kanál', 'názov'], $rows);
        }

        $this->newLine();
        $this->info(($this->option('fix') ? 'Presunutých' : 'Na presun') . ": {$moved}");
        $this->line("Video na YouTube nenájdené (zmazané/súkromné): {$notFoundOnYoutube}");
        $this->line("Kanál na YouTube nemá na webe záznam Canal: {$noCanalForChannel}");

        if (! $this->option('fix') && $rows !== []) {
            $this->warn('Nič sa nezapisovalo. Na zápis spustite príkaz s --fix.');
        }

        return self::SUCCESS;
    }
}
