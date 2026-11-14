<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RssController extends Controller
{
    /** Ako dlho sa odpoveď kanála považuje za čerstvú. */
    private const FRESH_MINUTES = 15;

    /**
     * Kanál je obmedzený zoznamom priamo na route (routes/api.php) — predtým
     * išla hodnota z URL nevalidovane do adresy pre file_get_contents.
     * Volanie navyše nemalo timeout, takže pomalý tkkbs.sk držal PHP proces
     * default_socket_timeout (typicky 60 s).
     *
     * Odpoveď sa drží 15 minút v cache, aby sa tkkbs.sk nesťahoval pri každej
     * požiadavke. Posledná úspešná odpoveď sa navyše odkladá natrvalo a pri
     * výpadku zdroja sa vráti ona namiesto chyby 502.
     */
    public function getRssCanal($canal)
    {
        $freshKey = "rss:{$canal}";
        $lastKey  = "rss:{$canal}:last";

        if (($cached = Cache::get($freshKey)) !== null) {
            return $cached;
        }

        [$data, $error] = $this->fetch($canal);

        if ($data !== null) {
            Cache::put($freshKey, $data, now()->addMinutes(self::FRESH_MINUTES));
            Cache::forever($lastKey, $data);

            return $data;
        }

        return Cache::get($lastKey)
            ?? response()->json(['message' => $error], 502);
    }

    /**
     * @return array{0: array|null, 1: string|null} dáta kanála alebo chybová správa
     */
    private function fetch(string $canal): array
    {
        try {
            $response = Http::timeout(5)
                ->retry(2, 300, throw: false)
                ->get("https://www.tkkbs.sk/rss/{$canal}/");
        } catch (ConnectionException) {
            return [null, 'RSS kanál je nedostupný.'];
        }

        if ($response->failed()) {
            return [null, 'RSS kanál je nedostupný.'];
        }

        $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        if ($xml === false) {
            return [null, 'RSS kanál sa nepodarilo prečítať.'];
        }

        return [json_decode(json_encode($xml), true), null];
    }

}
