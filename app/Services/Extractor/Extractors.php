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
use App\Models\Organization;
use App\Services\DetectService\DetectDateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Extractors
{
    public $organization;
    public $detectDateTime;


    public function setOrganization($id)
    {
        $this->organization = Organization::whereId($id)->first();
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

                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::warning('Zdroj modlitieb je nedostupný: ' . $e->getMessage(), ['url' => $url]);

            return null;
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
                'organization_id' => $item['organization'],
                'created_at' => Carbon::now()->subHours(2)->addMinute(rand(3, 55))->toDateTimeString(),
            ]);
        }
    }

}
