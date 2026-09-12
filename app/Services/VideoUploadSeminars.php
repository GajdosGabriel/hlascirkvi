<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.02.2019
 * Time: 17:01
 */

namespace App\Services;

use App\Enums\PostSection;
use App\Models\User;
use App\Models\Canal;
use Alaouy\Youtube\Youtube;
use App\Services\Images\StoreImage;
use App\Services\Images\YoutubeThumbnail;
use App\Notifications\Admin\Error;
use App\Models\Post;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentCanalRepository;
use App\Models\Seminar;

class VideoUploadSeminars
{
    public $organization;
    public $seminar;

    public function __construct(Seminar $seminar, Canal $organization)
    {
        $this->organization = $organization;
        $this->seminar = $seminar;
    }


    public function handle()
    {
        $this->validateUrlPlaylistOrChannel();
    }


    protected function validateUrlPlaylistOrChannel()
    {
        // Seminár bez playlistu končil na "Undefined variable $videoList".
        if (strlen((string) $this->seminar->youtube_playlist) < 8) {
            return;
        }

        $videoList = \Youtube::getPlaylistItemsByPlaylistId($this->seminar->youtube_playlist);

        $this->foreachVideolist($videoList['results'] ?? []);
    }


    protected function foreachVideolist($videoList)
    {
        foreach ($videoList as $video) {
            if (isset($video->contentDetails->upload->videoId)) {
                $videoId = $video->contentDetails->upload->videoId;
            } elseif (isset($video->contentDetails->playlistItem->resourceId->videoId)) {
                $videoId = $video->contentDetails->playlistItem->resourceId->videoId;
            } elseif (isset($video->snippet->resourceId->videoId)) {
                $videoId = $video->snippet->resourceId->videoId;
            } else {
                $this->sendErrorForAdmin();
                continue;
            }

            if ($this->checkIfVideoExist($videoId)) {
                continue;
            } else {
                $this->savePostVideo($video, $videoId);
            }
        }
    }

    protected function savePostVideo($video, $videoId)
    {
        $post =  $this->organization->posts()->create(
            [
                'title' => $video->snippet->title,
                'video_id' => $videoId,
                'body' => $video->snippet->description
            ]
        );

        StoreImage::for($post)->tryFromUrl(
            YoutubeThumbnail::bestUrl($video->snippet->thumbnails ?? null)
        );

        /*
         * Video seminára ide rovno do výpisu konferencií — inak by skončilo
         * v bufferi a čakalo na zverejnenie, hoci seminár si svoje videá
         * sťahuje sám a na mieru.
         */
        $post->update([
            'section'      => PostSection::Seminar,
            'published_at' => now(),
        ]);

        $post->seminars()->attach($this->seminar->id);
    }


    protected function sendErrorForAdmin()
    {
//        User::first()->notify(new Error($organization));
    }

    protected function checkIfVideoExist($videoId)
    {
        if ($post = Post::withTrashed()->whereVideoId($videoId)->first())
        {
            // Ak by bol vymazaný
            $post->restore();
            $post->seminars()->sync($this->seminar->id);
            return true;
        }
    }
}
