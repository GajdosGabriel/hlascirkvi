<?php

namespace App\Services\Youtube;

use App\Enums\CanalSection;
use App\Enums\PostSection;
use App\Models\Canal;
use App\Models\Post;
use App\Services\Images\StoreImage;
use App\Services\Images\YoutubeThumbnail;
use App\Services\VideoUploadFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Uloženie videí z YouTube ako príspevkov kanála. Jedna cesta pre denný
 * import, hľadanie podľa mena, ručné hľadanie aj semináre — predtým mala
 * každá vlastnú kópiu s inými kontrolami (niektorá neoverovala, či sa video
 * dá vložiť na web, iná nepoužila filter).
 *
 * Detaily videí sa pýtajú po päťdesiatich v jednom videos.list, nie video po
 * videu, a ukladá sa rovno aj trvanie a dátum zverejnenia na YouTube.
 */
class VideoImporter
{
    public function __construct(private ?YoutubeApi $api = null)
    {
        $this->api ??= app(YoutubeApi::class);
    }

    /**
     * ID, ktoré už v `posts` sú — aj zmazané a zablokované. Zmazaný príspevok
     * sa nemá pri ďalšom importe vrátiť.
     *
     * @param  string[]  $ids
     * @return string[]
     */
    public static function existingIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('posts')
            ->whereIn('video_id', array_values(array_unique($ids)))
            ->distinct()
            ->pluck('video_id')
            ->all();
    }

    /**
     * @param  string[]  $ids  ID videí v poradí, v akom prišli
     * @param  PostSection|null  $section  pevná sekcia (semináre); inak podľa kanála a buffera
     * @return Post[] novo uložené príspevky
     */
    public function import(Canal $canal, array $ids, ?PostSection $section = null): array
    {
        $ids = array_values(array_unique(array_filter($ids, [VideoId::class, 'isId'])));
        $new = array_values(array_diff($ids, self::existingIds($ids)));

        if ($new === []) {
            return [];
        }

        $videos = $this->api->videos($new, 'snippet,contentDetails,status');
        $saved = [];

        foreach ($new as $id) {
            $video = $videos[$id] ?? null;

            if ($video === null || ! $this->acceptable($video, $canal, $section)) {
                continue;
            }

            $saved[] = $this->save($canal, $video, $section);
        }

        return $saved;
    }

    /**
     * Zmazané a súkromné video YouTube vo videos.list nevráti vôbec, preto
     * tu stačí vložiteľnosť, filter kanála a ohlásený prenos.
     */
    protected function acceptable(object $video, Canal $canal, ?PostSection $section): bool
    {
        if (! ($video->status->embeddable ?? false) || ($video->status->privacyStatus ?? 'public') === 'private') {
            return false;
        }

        // Ohlásený prenos nemá obsah ani trvanie — v bežnom výpise by visel
        // prázdny. Kanál s prenosmi ho naopak chce hneď.
        if (($video->snippet->liveBroadcastContent ?? 'none') === 'upcoming'
            && $canal->post_section !== CanalSection::Live
            && $section === null) {
            return false;
        }

        return ! (new VideoUploadFilter($canal, (string) ($video->snippet->title ?? '')))->wordsChecker();
    }

    protected function save(Canal $canal, object $video, ?PostSection $section): Post
    {
        $publishedAt = $video->snippet->publishedAt ?? null;
        $duration = $video->contentDetails->duration ?? null;

        $post = $canal->posts()->create([
            // Stiahnuté video zatiaľ nie je zverejnené — `published_at` ostáva
            // prázdne, vypúšťa ho buffer (App\Services\Buffer).
            'title' => $video->snippet->title,
            'video_id' => $video->id,
            'body' => (string) ($video->snippet->description ?? ''),
            // Prebiehajúci prenos má trvanie P0D; skutočné doplní synchronizácia
            // komentárov, keď prenos skončí.
            'video_duration' => $duration && $duration !== 'P0D' ? $duration : null,
            'youtube_published_at' => $publishedAt
                ? Carbon::parse($publishedAt)->setTimezone(config('app.timezone'))
                : null,
        ]);

        StoreImage::for($post)->tryFromUrl(
            YoutubeThumbnail::bestUrl($video->snippet->thumbnails ?? null)
        );

        $target = $section
            ?? ($canal->post_section === CanalSection::Live ? PostSection::Live : null);

        if ($target !== null) {
            // Prenos bohoslužby aj video seminára sa zverejňujú hneď: čakať
            // deň v bufferi na prenos, ktorý práve beží, nemá zmysel.
            $post->update(['section' => $target, 'published_at' => now()]);
        } elseif (config('buffer.publish_on_import')) {
            $post->update(['published_at' => now()]);
        } elseif ($post->youtube_published_at?->lt(now()->subDays((int) config('buffer.archive_after_days')))) {
            // Staré video (kanálu dlho stál import) patrí v bufferi do archívu,
            // nie medzi čerstvé — inak by stovky starých videí jedného kanála
            // odsunuli nové. Buffer delí front podľa created_at a pri
            // zverejnení ho prepíše časom vydania.
            $post->forceFill(['created_at' => $post->youtube_published_at])->save();
        }

        return $post;
    }
}
