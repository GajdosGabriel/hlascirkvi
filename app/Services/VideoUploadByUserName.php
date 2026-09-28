<?php

namespace App\Services;

use App\Repositories\Eloquent\EloquentCanalRepository;
use App\Services\Youtube\VideoId;
use App\Services\Youtube\VideoImporter;
use App\Services\Youtube\VideoImportSchedule;
use App\Services\Youtube\YoutubeApi;
use App\Services\Youtube\YoutubeApiException;
use Illuminate\Support\Facades\Log;

/** Pravidelné hľadanie mien bez vlastného YouTube zdroja, vrátane zameškaných behov. */
class VideoUploadByUserName
{
    public function __construct(private ?YoutubeApi $api = null)
    {
        $this->api ??= app(YoutubeApi::class);
    }

    public function handle()
    {
        $canals = (new EloquentCanalRepository())->getUsersByDayOfWeek();
        // Aj položky, na ktoré dnes nevyjde rozpočet, ostanú splatné zajtra.
        \App\Models\Canal::whereKey($canals->modelKeys())->whereNull('video_check_next_at')
            ->update(['video_check_next_at' => now()]);
        $importer = new VideoImporter($this->api);
        $remaining = max(0, (int) config('youtube.name_search.max_pages_per_run', 40));

        foreach ($canals as $canal) {
            if ($remaining === 0) {
                break;
            }

            // Pevné okno a kurzor prežijú výpadok aj nedokončený beh. Úspech
            // zaznamenáme až po importe všetkých stránok, nie po prvom dopyte.
            if ($canal->name_search_window_end === null) {
                $canal->name_search_window_start = $canal->name_search_completed_until
                    ? $canal->name_search_completed_until->copy()->subDays((int) config('youtube.name_search.overlap_days', 2))
                    : now()->subDays((int) config('youtube.name_search.initial_days', 30));
                $canal->name_search_window_end = now();
            }
            $canal->video_check_next_at ??= now();
            $canal->video_check_attempted_at = now();
            $canal->save();

            try {
                $pages = min($remaining, max(1, (int) config('youtube.name_search.max_pages_per_canal', 3)));
                for ($page = 0; $page < $pages; $page++) {
                    $remaining--;
                    $response = $this->api->recentVideos(
                        $canal->title,
                        $canal->name_search_window_start->toRfc3339String(),
                        $canal->name_search_window_end->toRfc3339String(),
                        $canal->name_search_page_token,
                    );
                    $ids = array_values(array_filter(array_map([VideoId::class, 'from'], $response->items)));
                    $importer->import($canal, $ids);
                    $canal->name_search_page_token = $response->nextPageToken;
                    $canal->video_check_error = null;

                    if ($response->nextPageToken === null) {
                        $canal->name_search_completed_until = $canal->name_search_window_end;
                        $canal->name_search_window_start = null;
                        $canal->name_search_window_end = null;
                        VideoImportSchedule::succeeded($canal);
                        break;
                    }
                    $canal->save();
                }
            } catch (\Throwable $e) {
                // Expirovaný kurzor skúsime zajtra od začiatku rovnakého okna.
                // Už uložené videá importer rozpozná a nezdvojí.
                if ($e instanceof YoutubeApiException && $e->is('invalidPageToken')) {
                    $canal->name_search_page_token = null;
                }
                VideoImportSchedule::failed($canal, $e);
                Log::warning('Hľadanie videí podľa názvu kanála zlyhalo.', [
                    'canal_id' => $canal->id,
                    'exception' => get_class($e),
                ]);
                if ($e instanceof YoutubeApiException && $e->stopsRun()) {
                    break;
                }
            }
        }
    }
}
