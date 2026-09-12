<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.02.2019
 * Time: 17:01
 */

namespace App\Services;

use Alaouy\Youtube\Youtube;
use App\Repositories\Eloquent\EloquentCanalRepository;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Services\Images\StoreImage;
use App\Services\Images\YoutubeThumbnail;
use App\Services\Youtube\VideoId;
use Illuminate\Support\Facades\Log;


     // Hľadá názvy jednotlivých userov podla updater dni v týždni

class VideoUploadByUserName
{

    public function handle()
    {
        $organizations = (new EloquentCanalRepository())->getUsersByDayOfWeek();

        // Jeden neúspešný dopyt na YouTube (kvóta, výpadok) zhodil celý denný
        // beh, takže sa nespracovali ani ostatné kanály.
        foreach ($organizations as $organization) {
            try {
                // Set default parameters
                $params = [
                    'q'             => $organization->title,
                    'type'          => 'video',
                    'part'          => 'id,snippet',
                    'maxResults'    => 30
                ];

                // Bez výsledkov vracia balík false, nie prázdne pole, a foreach
                // nad ním v PHP 8 hlási "must be of type array|object".
                $videoList = \Youtube::searchAdvanced($params);
                $videoList = is_iterable($videoList) ? $videoList : [];

                $found = 0;
                $skipped = 0;

                foreach ($videoList as $video) {
                    $found++;

                    // Medzi výsledkami býva aj položka bez ID videa.
                    $videoId = VideoId::from($video);

                    if ($videoId === null) {
                        $skipped++;
                        continue;
                    }

                    if (!\DB::table('posts')->whereVideoId($videoId)->exists()) {
                        $post = $organization->posts()->create([
                            'title' => $video->snippet->title,
                            'video_id' => $videoId,
                            'body' => $video->snippet->description
                        ]);

                        StoreImage::for($post)->tryFromUrl(
                            YoutubeThumbnail::bestUrl($video->snippet->thumbnails ?? null)
                        );
                    }
                }

                // Preskočené položky sa inak stratia bez stopy. Zaujíma nás,
                // či ide o ojedinelý výsledok, alebo dopyt vracia samé nevideá.
                if ($skipped > 0) {
                    Log::info('Vo výsledkoch hľadania boli položky bez ID videa.', [
                        'organization_id' => $organization->id,
                        'title' => $organization->title,
                        'skipped' => $skipped,
                        'found' => $found,
                    ]);
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
