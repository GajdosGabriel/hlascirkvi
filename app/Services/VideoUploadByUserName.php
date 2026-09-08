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
use App\Services\Images\StoreImage;
use App\Services\Images\YoutubeThumbnail;
use Illuminate\Support\Facades\Log;


     // Hľadá názvy jednotlivých userov podla updater dni v týždni

class VideoUploadByUserName
{

    public function handle()
    {
        $organizations = (new EloquentOrganizationRepository())->getUsersByDayOfWeek();

        // Jeden neúspešný dopyt na YouTube (kvóta, výpadok) zhodil celý denný
        // beh, takže sa nespracovali ani ostatné kanály.
        foreach ($organizations as $organization) {
            try {
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

                        StoreImage::for($post)->tryFromUrl(
                            YoutubeThumbnail::bestUrl($video->snippet->thumbnails ?? null)
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Hľadanie videí podľa názvu kanála zlyhalo: ' . $e->getMessage(), [
                    'organization_id' => $organization->id,
                    'title' => $organization->title,
                ]);
            }
        }
    }
}
