<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Prepis reči z titulkov videa na YouTube (aj automatických).
 *
 * Oficiálne API vydá titulky len vlastníkovi kanála, preto sa ide cez
 * interné rozhranie prehrávača (klient ANDROID) — webový klient dnes vracia
 * prázdne titulky. Je to neoficiálna cesta: YouTube ju môže kedykoľvek
 * zmeniť; vtedy text() vráti null a zhrnutie sa urobí z popisu.
 *
 * Prepis sa drží v cache 30 dní — skúšanie rôznych rozsahov zhrnutia
 * nemá YouTube zakaždým volať znova.
 */
class YoutubeCaptions
{
    protected const CLIENT_VERSION = '20.10.38';

    /** Poradie jazykov; ručné titulky majú prednosť pred automatickými. */
    protected const LANGUAGES = ['sk', 'cs'];

    public function text(?string $videoId): ?string
    {
        if (! $videoId) {
            return null;
        }

        $text = Cache::remember('yt-captions:' . $videoId, now()->addDays(30), fn () => $this->fetch($videoId) ?? '');

        return $text === '' ? null : $text;
    }

    protected function fetch(string $videoId): ?string
    {
        try {
            $player = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'com.google.android.youtube/' . self::CLIENT_VERSION . ' (Linux; U; Android 14)'])
                ->post('https://www.youtube.com/youtubei/v1/player?prettyPrint=false', [
                    'context' => ['client' => [
                        'clientName' => 'ANDROID',
                        'clientVersion' => self::CLIENT_VERSION,
                        'androidSdkVersion' => 34,
                        'hl' => 'sk',
                    ]],
                    'videoId' => $videoId,
                ]);

            $track = $this->pickTrack((array) $player->json('captions.playerCaptionsTracklistRenderer.captionTracks'));

            if (! $track) {
                return null;
            }

            $url = preg_replace('/&fmt=[^&]*/', '', $track['baseUrl']) . '&fmt=json3';
            $events = (array) Http::timeout(20)->get($url)->json('events');
        } catch (Throwable $e) {
            Log::warning('YoutubeCaptions: titulky sa nepodarilo stiahnuť', ['video_id' => $videoId, 'error' => $e->getMessage()]);

            return null;
        }

        $text = collect($events)->flatMap(fn ($e) => $e['segs'] ?? [])->pluck('utf8')->implode('');

        // Značky automatických titulkov ([hudba], [potlesk]) nie sú reč.
        $text = preg_replace('/\[[^\]]{1,30}\]/u', ' ', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return $text === '' ? null : $text;
    }

    protected function pickTrack(array $tracks): ?array
    {
        foreach (self::LANGUAGES as $lang) {
            $inLang = array_filter($tracks, fn ($t) => str_starts_with($t['languageCode'] ?? '', $lang));

            // Ručné titulky nemajú „kind“, automatické majú kind=asr.
            foreach ([false, true] as $asr) {
                foreach ($inLang as $track) {
                    if ((($track['kind'] ?? '') === 'asr') === $asr) {
                        return $track;
                    }
                }
            }
        }

        return null;
    }
}
