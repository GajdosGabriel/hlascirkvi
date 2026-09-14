<?php

namespace App\Services;

use App\Enums\PostSection;
use App\Models\Canal;
use App\Models\Post;
use App\Models\Seminar;
use App\Services\Youtube\PlaylistId;
use App\Services\Youtube\VideoId;
use App\Services\Youtube\VideoImporter;
use App\Services\Youtube\YoutubeApi;

/**
 * Videá seminára z jeho playlistu. Seminár je uzavretý celok, preto sa
 * playlist prejde celý (predtým len prvých 50 položiek).
 */
class VideoUploadSeminars
{
    /** Poistka proti nekonečnému playlistu. */
    private const MAX_VIDEOS = 500;

    public function __construct(
        public Seminar $seminar,
        public Canal $organization,
        private ?YoutubeApi $api = null,
    ) {
        $this->api ??= app(YoutubeApi::class);
    }

    public function handle(): void
    {
        $playlistId = PlaylistId::fromInput($this->seminar->youtube_playlist);

        if ($playlistId === null) {
            return;
        }

        $ids = [];
        $token = null;

        do {
            $page = $this->api->playlistItems($playlistId, $token);
            $ids = array_merge($ids, array_filter(array_map([VideoId::class, 'from'], $page->items)));
            $token = $page->nextPageToken;
        } while ($token !== null && count($ids) < self::MAX_VIDEOS);

        // Video, ktoré už na webe je (aj zmazané), sa obnoví a priradí k semináru.
        Post::withTrashed()->whereIn('video_id', $ids)->get()->each(function (Post $post) {
            $post->restore();
            $post->seminars()->sync($this->seminar->id);
        });

        $saved = (new VideoImporter($this->api))->import($this->organization, $ids, PostSection::Seminar);

        foreach ($saved as $post) {
            $post->seminars()->attach($this->seminar->id);
        }
    }
}
