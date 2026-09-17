<?php

namespace App\Services\Youtube;

use App\Notifications\Admin\YoutubeApiKeyFailed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Upozorní superadminov, že YouTube odmieta API kľúč. Neplatný kľúč sa
 * predtým prejavil len varovaniami v logu a objavil sa náhodou.
 *
 * Synchronizácia komentárov beží každú hodinu, hlásenie preto chodí najviac
 * raz za deň. Vyčerpaná kvóta sem nepatrí — do rána sa obnoví sama.
 */
class KeyFailureAlert
{
    private const CACHE_KEY = 'youtube:key-failure-alert';

    public static function report(YoutubeApiException $e): void
    {
        if (! $e->isKeyFailure() || ! Cache::add(self::CACHE_KEY, true, now()->addDay())) {
            return;
        }

        try {
            Notification::send(NotifyAdmin::superadmins(), new YoutubeApiKeyFailed(Str::limit($e->getMessage(), 240)));
        } catch (\Throwable $mailError) {
            // Nefunkčná pošta nesmie zakryť pôvodnú chybu API.
            Log::error('Upozornenie na neplatný YouTube API kľúč sa nepodarilo odoslať: ' . $mailError->getMessage());
        }
    }
}
