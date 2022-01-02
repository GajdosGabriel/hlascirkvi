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


     // Hľadá názvy jednotlivých userov podla updater dni v týždni

class VideoUploadByUserName
{

    public function handle()
    {
        $organizations = (new EloquentOrganizationRepository())->getUsersByDayOfWeek();

        foreach ($organizations as $organization) {
            // Set default parameters
            $params = [
                'q'             => $organization->title,
                'type'          => 'video',
                'part'          => 'id, snippet',
                'maxResults'    => 30
            ];

            $videoList = \Youtube::searchAdvanced($params);

            foreach ($videoList as $video) {
                if (!\DB::table('posts')->whereVideoId($video->id->videoId)->exists()) {
                    $post = $organization->posts()->create([
                        'title' => $video->snippet->title,
                        'video_id' => $video->id->videoId,
                        'body' => $video->snippet->description
                    ]);

                    (new ImageResize())->resizeImage($post, $video->snippet->thumbnails->medium->url);
                }
            }
        }
    }
}
