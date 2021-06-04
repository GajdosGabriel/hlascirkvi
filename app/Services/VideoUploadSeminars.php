<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.02.2019
 * Time: 17:01
 */

namespace App\Services;

use App\User;
use App\Organization;
use Alaouy\Youtube\Youtube;
use App\Services\ImageResize;
use App\Notifications\Admin\Error;
use App\Post;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\Seminar;

class VideoUploadSeminars
{
    public $organization;
    public $seminar;

    public function __construct(Seminar $seminar, Organization $organization)
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
        if (strlen($this->seminar->youtube_playlist) > 7) {
            $videoList = \Youtube::getPlaylistItemsByPlaylistId($this->seminar->youtube_playlist);
            $videoList = $videoList['results'];
        }

        $this->foreachVideolist($videoList);
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

        if (isset($video->snippet->thumbnails->medium->url)) {
            (new ImageResize())->resizeImage($post, $video->snippet->thumbnails->medium->url);
        }

        /*
         * Zaradiť do zoznamu (17- Semináre)
         * lebo inak by skončilo v Buffer a čakať na publikovanie.
         */
        $post->updaters()->attach(17);

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
