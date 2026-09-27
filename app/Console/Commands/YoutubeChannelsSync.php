<?php

namespace App\Console\Commands;

use App\Enums\CanalIdentityMode;
use App\Enums\Denomination;
use App\Models\Canal;
use App\Services\Youtube\ChannelId;
use App\Services\Youtube\PlaylistId;
use App\Services\Youtube\YoutubeApi;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Rozšírenie ponuky o kresťanské kanály (9/2026) a kontrola kanálov, ktoré
 * roky nič nestiahli. Každý kanál sa overí v API — odberatelia a dátum
 * posledného videa; neaktívny sa nezapíše.
 *
 * Bez --apply len vypíše návrh, rovnako ako youtube:fix-channel-ids.
 */
class YoutubeChannelsSync extends Command
{
    protected $signature = 'youtube:channels
        {--apply : Zapísať aktívne kanály do databázy}
        {--include-review : Zapísať aj kanály označené na ručné overenie}';

    protected $description = 'Overí a doplní nové kresťanské YouTube kanály a skontroluje stojace kanály';

    /** Kanál bez videa za toto obdobie sa nepridáva. */
    private const ACTIVE_MONTHS = 12;

    private const BRATISLAVA = 242;

    private const CELE_SLOVENSKO = 4209;

    /**
     * `id` = existujúci kanál na webe, ktorému sa zdroj aktualizuje.
     * `review` = neoficiálny alebo neistý kanál, zapíše sa len s --include-review.
     */
    private function channels(): array
    {
        $catholic = Denomination::Catholic;
        $community = CanalIdentityMode::Organization;
        $person = CanalIdentityMode::Personal;

        return [
            // Aktualizácia existujúcich
            ['id' => 270, 'title' => 'Bratislavské Hanusove Dni', 'source' => '@HanusoveDni', 'identity_mode' => $community, 'denomination' => $catholic, 'clear_playlist' => true],
            ['id' => 755, 'title' => 'EVS', 'source' => '@EVSchcemviac', 'identity_mode' => $community, 'denomination' => Denomination::Evangelical],
            ['id' => 15, 'title' => 'Kuffa Marian', 'source' => '@mariankuffa-prednaskyakazn5504', 'identity_mode' => $person, 'denomination' => $catholic, 'review' => 'fanúšikovský kanál, osobnosť sa dnes hľadá podľa mena'],

            // Nové — spoločenstvá a médiá
            ['title' => 'Rádio Lumen', 'source' => '@radiolumen4642', 'identity_mode' => $community, 'denomination' => $catholic, 'village_id' => self::BRATISLAVA],
            ['title' => 'Rádio Mária Slovensko', 'source' => 'https://www.youtube.com/radiomariaslovensko', 'identity_mode' => $community, 'denomination' => $catholic, 'village_id' => self::BRATISLAVA],
            ['title' => 'Katolícke noviny', 'source' => 'UCjSzUTjmcdQGoHSH63nknlA', 'identity_mode' => $community, 'denomination' => $catholic, 'village_id' => self::BRATISLAVA],
            ['title' => 'Zachej', 'source' => '@ZachejSk', 'identity_mode' => $community, 'denomination' => $catholic],
            ['title' => 'Komunita Emanuel', 'source' => 'UCJAK7iG2vlBlHJKbRIKuXow', 'identity_mode' => $community, 'denomination' => $catholic],
            ['title' => 'Dominikáni Zvolen', 'source' => 'UC0l280timGFEAREcsWL5neg', 'identity_mode' => $community, 'denomination' => $catholic],
            ['title' => 'Kláštor kapucínov Bratislava', 'source' => 'UCla0QDqraH3xzCMnqXWA_Sw', 'identity_mode' => $community, 'denomination' => $catholic, 'village_id' => self::BRATISLAVA],
            ['title' => 'Dóm sv. Martina', 'source' => 'UCUK74ySS52sCTDLCld_sM3g', 'identity_mode' => $community, 'denomination' => $catholic, 'village_id' => self::BRATISLAVA],
            ['title' => 'TV Noe', 'source' => '@tv_noe', 'identity_mode' => $community, 'denomination' => $catholic],
            ['title' => 'Radio Proglas', 'source' => 'UCMGxWQFuxt50mlCE0D1G7sA', 'identity_mode' => $community, 'denomination' => $catholic],

            // Nové — osobnosti
            ['title' => 'Heryán Ladislav', 'source' => '@ladisdb', 'identity_mode' => $person, 'denomination' => $catholic],
            ['title' => 'Vácha Marek Orko', 'source' => '@marekvacha3974', 'identity_mode' => $person, 'denomination' => $catholic, 'review' => 'neoverené, či je kanál oficiálny'],
        ];
    }

    /** Kanály, ktoré roky nič nestiahli — len kontrola, bez zápisu. */
    private const STALE = [263, 268, 273, 280, 30, 370, 393, 758];

    public function handle(YoutubeApi $api): int
    {
        $rows = [];

        foreach ($this->channels() as $entry) {
            $rows[] = $this->process($api, $entry);
        }

        $this->table(['kanál', 'ID', 'odberatelia', 'posledné video', 'výsledok'], $rows);

        $this->newLine();
        $this->info('Stojace kanály:');
        $this->table(['id', 'kanál', 'zdroj', 'posledné video na YouTube', 'posledný import'], $this->stale($api));

        if (! $this->option('apply')) {
            $this->warn('Nič sa nezapisovalo. Na zápis spustite príkaz s --apply.');
        }

        return self::SUCCESS;
    }

    private function process(YoutubeApi $api, array $entry): array
    {
        try {
            $channelId = ChannelId::resolve($entry['source']);
        } catch (\Throwable $e) {
            return [$entry['title'], $entry['source'], '', '', 'chyba API: ' . $e->getMessage()];
        }

        if ($channelId === null) {
            return [$entry['title'], $entry['source'], '', '', 'kanál sa nenašiel'];
        }

        $channel = $api->channels([$channelId])[$channelId] ?? null;
        $last = $this->lastUpload($api, YoutubeApi::uploadsPlaylistId($channelId));
        $row = [
            $entry['title'] . ($channel ? ' („' . $channel->snippet->title . '“)' : ''),
            $channelId,
            number_format((int) ($channel->statistics->subscriberCount ?? 0), 0, ',', ' '),
            $last?->toDateString() ?? '—',
        ];

        if ($last === null || $last->lt(now()->subMonths(self::ACTIVE_MONTHS))) {
            return [...$row, 'preskočené: neaktívny'];
        }

        $existing = Canal::withTrashed()->where('youtube_channel', $channelId)->first();

        if ($existing && $existing->id !== ($entry['id'] ?? null)) {
            return [...$row, 'už na webe ako #' . $existing->id . ' ' . $existing->title];
        }

        if (isset($entry['review']) && ! $this->option('include-review')) {
            return [...$row, 'na overenie: ' . $entry['review']];
        }

        if (! $this->option('apply')) {
            return [...$row, isset($entry['id']) ? 'aktualizovať #' . $entry['id'] : 'pridať'];
        }

        return [...$row, $this->write($entry, $channelId)];
    }

    private function write(array $entry, string $channelId): string
    {
        $attributes = [
            'youtube_channel' => $channelId,
            'identity_mode' => $entry['identity_mode'],
            'denomination' => $entry['denomination'],
            'youtube_disabled_at' => null,
            'youtube_disabled_reason' => null,
        ];

        if ($entry['clear_playlist'] ?? false) {
            $attributes['youtube_playlist'] = null;
        }

        if (isset($entry['id'])) {
            Canal::findOrFail($entry['id'])->forceFill($attributes)->save();

            return 'aktualizované #' . $entry['id'];
        }

        $canal = Canal::create($attributes + [
            'title' => $entry['title'],
            'village_id' => $entry['village_id'] ?? self::CELE_SLOVENSKO,
            'identity_mode' => \App\Enums\CanalIdentityMode::Organization,
        ]);

        return 'pridané #' . $canal->id;
    }

    private function stale(YoutubeApi $api): array
    {
        return Canal::whereIn('id', self::STALE)->withMax('posts', 'created_at')->get()
            ->map(function (Canal $canal) use ($api) {
                $playlist = ChannelId::isId($canal->youtube_channel)
                    ? YoutubeApi::uploadsPlaylistId($canal->youtube_channel)
                    : PlaylistId::fromInput($canal->youtube_playlist);

                try {
                    $last = $playlist ? $this->lastUpload($api, $playlist)?->toDateString() ?? '—' : 'bez ID';
                } catch (\Throwable $e) {
                    $last = 'chyba: ' . $e->getMessage();
                }

                return [
                    $canal->id,
                    $canal->title,
                    $canal->youtube_channel ?: $canal->youtube_playlist,
                    $last,
                    $canal->posts_max_created_at,
                ];
            })
            ->all();
    }

    private function lastUpload(YoutubeApi $api, string $playlistId): ?Carbon
    {
        try {
            $published = $api->playlistItems($playlistId, null, 1)->items[0]->contentDetails->videoPublishedAt ?? null;
        } catch (\Throwable $e) {
            return null;
        }

        return $published ? Carbon::parse($published) : null;
    }
}
