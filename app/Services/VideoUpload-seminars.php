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
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentOrganizationRepository;

class VideoUpload
{
    public $organization;
    public $idPlayList;

    public function __construct(Organization $organization, $idPlayList)
    {
        $this->organization = $organization;
        $this->idPlayList = $idPlayList;
    }


    public function handle()
    {
        $this->validateUrlPlaylistOrChannel();
    }


    protected function validateUrlPlaylistOrChannel()
    {
        if (strlen($this->idPlayList) > 7) {
            $videoList = \Youtube::getPlaylistItemsByPlaylistId($this->idPlayList);
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

            if ($this->checkIfVideoExist($videoId, $video)) {
                continue;
            } else {
                $this->savePostVideo($video, $videoId);
            }
        }
    }

    protected function savePostVideo($video, $videoId)
    {
        $post =  $this->organizations->createPost(
            $this->organization->id,
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
         * bude sa hned publikovať.
         */
        $post->updaters()->attach(17);
    }


    protected function sendErrorForAdmin()
    {
//        User::first()->notify(new Error($organization));
    }

    protected function checkIfVideoExist($videoId, $video)
    {
        if (\DB::table('posts')->whereVideoId($videoId)->first()) {
            return true;
        }
    }
}
