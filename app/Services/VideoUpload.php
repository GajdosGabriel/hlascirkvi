<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.02.2019
 * Time: 17:01
 */

namespace App\Services;

use Alaouy\Youtube\Youtube;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Services\ImageResize;
use App\Models\User;
use App\Notifications\Admin\Error;


class VideoUpload
{

    public function __construct()
    {
        $this->organizations = new EloquentOrganizationRepository;
    }


    public function handle()
    {
        $this->foreachOrganization();
    }


    protected function foreachOrganization()
    {
        foreach ($this->organizations->getYoutubeVideos() as $organization) {
            $this->validateUrlPlaylistOrChannel($organization);
        }
    }


    protected function validateUrlPlaylistOrChannel($organization)
    {
        if (strlen($organization->youtube_channel) > 7)
            $videoList = \Youtube::getActivitiesByChannelId($organization->youtube_channel);

        if (strlen($organization->youtube_playlist) > 7) {
            $videoList = \Youtube::getPlaylistItemsByPlaylistId($organization->youtube_playlist);
            $videoList = $videoList['results'];
        }

        $this->foreachVideolist($videoList, $organization);
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

        $post =  $this->organizations->createPost(
            $organization->id,
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
         * Ak je organizácia zaradená do zoznamu (1-živé vysielanie)
         * bude sa hned publikovať.
         */
        if ($organization->updaters->contains('id', 1)) {
            $post->updaters()->attach(16);
        }


        /*
        * Ak je organizácia zaradená do zoznamu (15 front-post)
        * bude sa hned publikovať.
        */
        if ($organization->updaters->contains('id', 4)) {
            $post->updaters()->attach(15);
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
        if ($organization->id == 256) {

            // title of video
            $str = strtolower($video->snippet->title);
            $substr = strtolower('Bohoslužby Banská Bystrica');

            if (strpos($str, $substr) !== false) {
                return false;
            } else {
                return true;
            }
        }
    }
}
