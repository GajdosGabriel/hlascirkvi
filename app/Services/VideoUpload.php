<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.02.2019
 * Time: 17:01
 */

namespace App\Services;

use App\Enums\CanalSection;
use App\Enums\PostSection;
use App\Models\User;
use Alaouy\Youtube\Youtube;
use App\Services\Images\StoreImage;
use App\Services\Images\YoutubeThumbnail;
use App\Notifications\Admin\Error;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentCanalRepository;
use App\Services\Youtube\ChannelId;
use App\Services\Youtube\DisableImport;
use App\Services\Youtube\NotifyAdmin;
use App\Services\Youtube\PlaylistId;
use Illuminate\Support\Facades\Log;


class VideoUpload
{

    protected $organizations;

    public function __construct()
    {
        $this->organizations = new EloquentCanalRepository;
    }


    public function handle()
    {
        // Zoznam už rozposlaných hlásení patrí jednému behu importu.
        NotifyAdmin::forget();

        $this->foreachOrganization();
    }


    /**
     * Import beží denne nad stovkami kanálov. Jeden nedostupný kanál, vyčerpaná
     * kvóta YouTube API alebo zmazaný playlist zhodili celý beh a zvyšné kanály
     * v ten deň neprešli vôbec — preto je každý kanál v samostatnom try/catch.
     */
    protected function foreachOrganization()
    {
        foreach ($this->organizations->getYoutubeVideos() as $organization) {
            try {
                $this->validateUrlPlaylistOrChannel($organization);
            } catch (\Throwable $e) {
                Log::warning('Import videí z YouTube zlyhal: ' . $e->getMessage(), [
                    'organization_id' => $organization->id,
                    'channel' => $organization->youtube_channel,
                    'playlist' => $organization->youtube_playlist,
                ]);
            }
        }
    }


    /**
     * Kanál môže mať zadaný kanál na YouTube, playlist alebo oboje. Keď sa
     * ani jeden zo zadaných zdrojov na YouTube nenájde, sťahovanie sa kanálu
     * vypne a superadminovi príde notifikácia — doteraz sa tá istá chyba
     * písala do logu každý deň a nikto o nej nevedel.
     */
    protected function validateUrlPlaylistOrChannel($organization)
    {
        $sources = [];

        if (trim((string) $organization->youtube_channel) !== '') {
            $sources[] = $this->fromChannel($organization);
        }

        if (trim((string) $organization->youtube_playlist) !== '') {
            $sources[] = $this->fromPlaylist($organization);
        }

        if ($sources === []) {
            return;
        }

        $missing = array_values(array_filter(array_column($sources, 'missing')));

        // Vypíname len kanál, ktorému nezostal žiadny funkčný zdroj. Keď mu
        // druhý zdroj beží, import má odkiaľ brať a stačí notifikácia.
        if (count($missing) === count($sources)) {
            DisableImport::because($organization, implode('; ', $missing));

            return;
        }

        // Notifikácia, nie log: chýbajúci zdroj je preklep alebo starý údaj
        // vo formulári kanála a opraviť ho vie len správca.
        foreach ($missing as $reason) {
            NotifyAdmin::about(
                $organization,
                'source-missing',
                $organization->title . ': ' . $reason
                    . '. Videá zatiaľ chodia z druhého zdroja — údaj opravte alebo vymažte vo formulári kanála.'
            );
        }

        // $videoList tu nebola inicializovaná — kanál bez youtube_channel aj bez
        // youtube_playlist (alebo s null, kde strlen() v PHP 8.1+ navyše hlási
        // deprecation) skončil na "Undefined variable $videoList".
        $videoList = [];

        // Playlist má prednosť pred kanálom — tak to bolo aj doteraz.
        foreach ($sources as $source) {
            if ($source['videos'] !== null) {
                $videoList = $source['videos'];
            }
        }

        if (empty($videoList)) {
            return;
        }

        $this->foreachVideolist($videoList, $organization);
    }


    /**
     * Videá z kanála. Adresu kanála (https://www.youtube.com/@meno) prepíše
     * na ID a uloží — formulár to už robí sám, v databáze však staré hodnoty
     * zostali a YouTube na takéto `channelId` odpovedá chybou 403
     * „The request is not properly authorized", nie prázdnym zoznamom.
     *
     * @return array{videos: ?array, missing: ?string}
     */
    protected function fromChannel($organization): array
    {
        $raw = trim((string) $organization->youtube_channel);
        $channelId = ChannelId::fromInput($raw);

        if ($channelId === null) {
            $channelId = ChannelId::resolve($raw);

            if ($channelId === null) {
                return ['videos' => null, 'missing' => 'kanál „' . $raw . '" sa na YouTube nenašiel'];
            }

            $organization->forceFill(['youtube_channel' => $channelId])->save();

            NotifyAdmin::about(
                $organization,
                'channel-rewritten',
                $organization->title . ': adresa kanála YouTube „' . $raw . '" bola prepísaná na ID '
                    . $channelId . '. Skontrolujte vo formulári kanála, či ide o správny kanál.'
            );
        }

        try {
            return ['videos' => \Youtube::getActivitiesByChannelId($channelId), 'missing' => null];
        } catch (\Throwable $e) {
            // Na neexistujúci channelId odpovedá YouTube tou istou chybou 403
            // ako pri chybnej autorizácii, takže či kanál naozaj zmizol,
            // povie až channels.list.
            if ($this->channelIsGone($channelId)) {
                return ['videos' => null, 'missing' => 'kanál ' . $channelId . ' na YouTube už neexistuje'];
            }

            throw $e;
        }
    }


    /**
     * @return array{videos: ?array, missing: ?string}
     */
    protected function fromPlaylist($organization): array
    {
        $raw = trim((string) $organization->youtube_playlist);
        $playlistId = PlaylistId::fromInput($raw);

        if ($playlistId === null) {
            return ['videos' => null, 'missing' => 'playlist „' . $raw . '" nie je ID playlistu'];
        }

        try {
            return ['videos' => \Youtube::getPlaylistItemsByPlaylistId($playlistId)['results'], 'missing' => null];
        } catch (\Throwable $e) {
            // Zmazaný playlist YouTube pomenuje priamo.
            if (str_contains($e->getMessage(), 'playlistNotFound')) {
                return ['videos' => null, 'missing' => 'playlist ' . $playlistId . ' na YouTube už neexistuje'];
            }

            throw $e;
        }
    }


    protected function channelIsGone(string $channelId): bool
    {
        try {
            // Balík vracia pri prázdnej odpovedi false, nie objekt.
            return ! is_object(\Youtube::getChannelById($channelId, [], ['id']));
        } catch (\Throwable $e) {
            // Vyčerpaná kvóta ani výpadok API neznamenajú zmazaný kanál —
            // v takom prípade import radšej nevypíname.
            return false;
        }
    }


    protected function foreachVideolist($videoList,  $organization)
    {

        foreach ($videoList as $video) {
            if (isset($video->contentDetails->upload->videoId)) {
                $videoId = $video->contentDetails->upload->videoId;
            } elseif (isset($video->contentDetails->playlistItem->resourceId->videoId)) {
                $videoId = $video->contentDetails->playlistItem->resourceId->videoId;
            } elseif (isset($video->snippet->resourceId->videoId)) {
                $videoId = $video->snippet->resourceId->videoId;
            } else {
                $this->sendErrorForAdmin($organization);
                continue;
            }

            if ($this->checkIfVideoExist($videoId, $video, $organization)) {
                continue;
            } else {
                $this->savePostVideo($video, $videoId, $organization);
            }
        }
    }

    protected function savePostVideo($video, $videoId, $organization)
    {
        // Chceck if video is embededable
        $video = \Youtube::getVideoInfo($videoId);

        // If video isnt shareing for public // is private
        if (!$video->status->embeddable) {
            return;
        };

        // Video filter 
        $filter = new VideoUploadFilter($organization, $video->snippet->title);

        if ($filter->wordsChecker() ) {
            return;
        };

        $post = $this->organizations->createPost(
            $organization->id,
            [
                // Stiahnuté video zatiaľ nie je zverejnené — `published_at`
                // ostáva prázdne. Predtým sa tu vypĺňal stĺpec `published`,
                // podľa ktorého potom polovica aplikácie brala čerstvý import
                // ako hotovú publikáciu.
                'title'     => $video->snippet->title,
                'video_id'  => $videoId,
                'body'      => $video->snippet->description,
            ]
        );

        StoreImage::for($post)->tryFromUrl(
            YoutubeThumbnail::bestUrl($video->snippet->thumbnails ?? null)
        );

        /*
         * Kanál s prenosmi bohoslužieb (post_section = live) zverejňuje hneď:
         * čakať deň v bufferi na prenos, ktorý práve beží, nemá zmysel.
         * Zaradenie predtým nieslo priradenie updatera 1 na kanáli.
         */
        if ($organization->post_section === CanalSection::Live) {
            $post->update([
                'section'      => PostSection::Live,
                'published_at' => now(),
            ]);

            return;
        }

        /*
        * Ostatné kanály idú do buffera — publisher ich vypustí po jednom
        * počas dňa (App\Services\Buffer). Predtým sa updater pripájal rovno
        * tu, takže celý denný import (okolo 13 videí) naskočil do zoznamu
        * v jednej sekunde o 16:24; presne tomu má buffer zabrániť. Späť sa to
        * prepne cez BUFFER_PUBLISH_ON_IMPORT=true.
        *
        * Poistka platila len pre kanály zo zoznamu „default" (updater 4),
        * teda pre dvadsať z piatich stoviek — čo bol zvyšok pôvodného
        * číselníka, nie zámer. Teraz platí pre každý kanál, ktorý ide cez
        * buffer.
        */
        if (config('buffer.publish_on_import')) {
            $post->update(['published_at' => now()]);
        }
    }


    protected function sendErrorForAdmin($organization)
    {
        //        User::first()->notify(new Error($organization));
    }

    protected function checkIfVideoExist($videoId, $video, $organization)
    {
        if (\DB::table('posts')->whereVideoId($videoId)->first())  return true;

        return  $this->exemption($video, $organization);
    }

    protected function exemption($video, $organization)
    {
        // if ($organization->id == 256) {

        //     // title of video
        //     $str = strtolower($video->snippet->title);
        //     $substr = strtolower('Bohoslužby Banská Bystrica');

        //     if (strpos($str, $substr) !== false) {
        //         return false;
        //     } else {
        //         return true;
        //     }
        // }

        return false;
    }
}
