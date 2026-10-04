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
        public Canal $canal,
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

        (new VideoImporter($this->api))->import($this->canal, $ids,
            $this->seminar->kind === 'collection' ? null : PostSection::Seminar);

        // Opakovaný import iba doplní väzby. Neobnovuje zmazané videá a neodoberá iné kolekcie.
        $posts = Post::whereIn('video_id', $ids)
            ->where(fn ($q) => $q->where('canal_id', $this->canal->id)
                ->orWhere(fn ($public) => $public->published()->available()
                    ->whereHas('canal', fn ($canal) => $canal->whereNotNull('published'))))
            ->pluck('id');
        $this->seminar->posts()->syncWithoutDetaching($posts);
    }
}
