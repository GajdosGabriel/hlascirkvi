<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21. 12. 2019
 * Time: 10:35
 */

namespace App\Services\Extractor;

use DB;
use Carbon\Carbon;
use App\Models\Canal;
use App\Services\DetectService\DetectDateTime;
use App\Services\SystemLog\Recorder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Extractors
{
    public $canal;
    public $detectDateTime;


    public function setCanal($id)
    {
        $this->canal = Canal::whereId($id)->first();
        $this->detectDateTime = new DetectDateTime();
    }

    /**
     * Stiahne stránku, z ktorej sa ťahajú modlitbové úmysly.
     *
     * Potomkovia tu mali `file_get_contents($this->url)` — bez timeoutu (platil
     * default_socket_timeout, typicky 60 s) a bez kontroly návratovej hodnoty,
     * takže pri výpadku cieľového webu prišlo `false` a to skončilo
     * v DOMDocument::loadHTML(). Príkazy pritom bežia každú hodinu.
     *
     * Vracia null, keď sa stránku nepodarilo stiahnuť — volajúci má skončiť.
     */
    protected function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 500)
                ->withHeaders(['User-Agent' => 'hlascirkvi.sk (prayer reader)'])
                ->get($url);

            if ($response->failed()) {
                Log::warning('Zdroj modlitieb odpovedal chybou.', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                $this->recordUnavailable($url, 'HTTP ' . $response->status());

                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::warning('Zdroj modlitieb je nedostupný: ' . $e->getMessage(), ['url' => $url]);

            $this->recordUnavailable($url, $e->getMessage());

            return null;
        }
    }

    /** Príkazy bežia každú hodinu; jeden záznam za výpadok a deň stačí. */
    protected function recordUnavailable(string $url, string $reason): void
    {
        if (Recorder::onceIn(24 * 60, 'prayer-source:' . md5($url))) {
            Recorder::warning('prayer', 'source_unavailable', 'Zdroj modlitieb nedostupný: ' . parse_url($url, PHP_URL_HOST),
                status: 'failed',
                context: ['url' => $url, 'error' => $reason],
            );
        }
    }

    protected function createPrayer($data)
    {

        foreach ($data as $item) {

            // Find or create new record
            if (DB::table('prayers')->whereBody($item['body'])->first()) {
                continue;
            }

            DB::table('prayers')->insert([
                'title' => isset($item['title'])  ? $item['title'] : '',
                'body' => $item['body'],
                'user_name' => $item['user'],
                'canal_id' => $item['canal'],
                'created_at' => Carbon::now()->subHours(2)->addMinute(rand(3, 55))->toDateTimeString(),
            ]);
        }
    }

}
