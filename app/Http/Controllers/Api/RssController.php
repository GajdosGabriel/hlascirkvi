<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class RssController extends Controller
{
    /**
     * Kanál je obmedzený zoznamom priamo na route (routes/api.php) — predtým
     * išla hodnota z URL nevalidovane do adresy pre file_get_contents.
     * Volanie navyše nemalo timeout, takže pomalý tkkbs.sk držal PHP proces
     * default_socket_timeout (typicky 60 s).
     */
    public function getRssCanal($canal)
    {
        $response = Http::timeout(5)->retry(2, 300)->get("https://www.tkkbs.sk/rss/{$canal}/");

        if ($response->failed()) {
            return response()->json(['message' => 'RSS kanál je nedostupný.'], 502);
        }

        $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NONET);

        if ($xml === false) {
            return response()->json(['message' => 'RSS kanál sa nepodarilo prečítať.'], 502);
        }

        return json_decode(json_encode($xml), true);
    }

}
