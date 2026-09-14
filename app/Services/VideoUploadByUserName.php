<?php

namespace App\Services;

use App\Repositories\Eloquent\EloquentCanalRepository;
use App\Services\Youtube\VideoId;
use App\Services\Youtube\VideoImporter;
use App\Services\Youtube\YoutubeApi;
use App\Services\Youtube\YoutubeApiException;
use Illuminate\Support\Facades\Log;

/**
 * Hľadá videá osobností bez vlastného kanála podľa mena, v deň z
 * `organizations.import_day`. Search stojí sto jednotiek kvóty, preto
 * organizácie s kanálom alebo playlistom idú cez VideoUpload.
 */
class VideoUploadByUserName
{
    public function __construct(private ?YoutubeApi $api = null)
    {
        $this->api ??= app(YoutubeApi::class);
    }

    public function handle()
    {
        $organizations = (new EloquentCanalRepository())->getUsersByDayOfWeek();
        $importer = new VideoImporter($this->api);

        // Jeden neúspešný dopyt na YouTube (výpadok) nesmie zhodiť celý denný
        // beh, vyčerpaná kvóta ho však ukončí.
        foreach ($organizations as $organization) {
            try {
                $items = $this->api->searchVideos($organization->title, 30);
                $ids = array_values(array_filter(array_map([VideoId::class, 'from'], $items)));

                // Preskočené položky sa inak stratia bez stopy. Zaujíma nás,
                // či ide o ojedinelý výsledok, alebo dopyt vracia samé nevideá.
                if (count($ids) < count($items)) {
                    Log::info('Vo výsledkoch hľadania boli položky bez ID videa.', [
                        'organization_id' => $organization->id,
                        'title' => $organization->title,
                        'skipped' => count($items) - count($ids),
                        'found' => count($items),
                    ]);
                }

                $importer->import($organization, $ids);
            } catch (\Throwable $e) {
                Log::warning('Hľadanie videí podľa názvu kanála zlyhalo: ' . $e->getMessage(), [
                    'organization_id' => $organization->id,
                    'title' => $organization->title,
                ]);

                if ($e instanceof YoutubeApiException && $e->is('quotaExceeded')) {
                    break;
                }
            }
        }
    }
}
