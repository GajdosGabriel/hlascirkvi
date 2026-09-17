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
 * `canals.import_day`. Search stojí sto jednotiek kvóty, preto
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
        $canals = (new EloquentCanalRepository())->getUsersByDayOfWeek();
        $importer = new VideoImporter($this->api);

        // Jeden neúspešný dopyt na YouTube (výpadok) nesmie zhodiť celý denný
        // beh, vyčerpaná kvóta ho však ukončí.
        foreach ($canals as $canal) {
            try {
                $items = $this->api->searchVideos($canal->title, 30);
                $ids = array_values(array_filter(array_map([VideoId::class, 'from'], $items)));

                // Preskočené položky sa inak stratia bez stopy. Zaujíma nás,
                // či ide o ojedinelý výsledok, alebo dopyt vracia samé nevideá.
                if (count($ids) < count($items)) {
                    Log::info('Vo výsledkoch hľadania boli položky bez ID videa.', [
                        'canal_id' => $canal->id,
                        'title' => $canal->title,
                        'skipped' => count($items) - count($ids),
                        'found' => count($items),
                    ]);
                }

                $importer->import($canal, $ids);
            } catch (\Throwable $e) {
                Log::warning('Hľadanie videí podľa názvu kanála zlyhalo: ' . $e->getMessage(), [
                    'canal_id' => $canal->id,
                    'title' => $canal->title,
                ]);

                if ($e instanceof YoutubeApiException && $e->stopsRun()) {
                    break;
                }
            }
        }
    }
}
