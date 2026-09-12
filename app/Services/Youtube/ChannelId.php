<?php

namespace App\Services\Youtube;

/**
 * Do `organizations.youtube_channel` patrí ID kanála (UC + 22 znakov).
 * Do formulára sa však dá vložiť aj adresa kanála a YouTube API na
 * `activities.list?channelId=https://www.youtube.com/@handle` neodpovie
 * prázdnym zoznamom, ale chybou 403 „The request is not properly
 * authorized" — denný import takého kanála tak padal stále rovnako
 * (v produkcii kanály 755 EVS a 758 Fatima TV).
 *
 * Trieda vstup zjednotí: ID vyreže z adresy lokálne, handle (@meno,
 * /c/meno, /user/meno) doloží z API.
 */
class ChannelId
{
    /** ID kanála: UC + 22 znakov base64url. */
    public const PATTERN = '/^UC[A-Za-z0-9_-]{22}$/';

    /**
     * Cesty, ktoré kanál neurčujú — z `youtube.com/watch` ani
     * `youtube.com/playlist` sa handle vyrábať nemá.
     */
    private const RESERVED = [
        'watch', 'playlist', 'playlists', 'embed', 'shorts', 'live', 'feed',
        'results', 'channel', 'hashtag', 'about', 'account', 'oembed',
        'videos', 'streams', 'community', 'featured',
    ];

    public static function isId(?string $value): bool
    {
        return is_string($value) && preg_match(self::PATTERN, trim($value)) === 1;
    }

    /**
     * ID kanála bez dopytu na API: buď samotné ID, alebo adresa s /channel/UC….
     */
    public static function fromInput(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (self::isId($value)) {
            return $value;
        }

        if (preg_match('~/channel/(UC[A-Za-z0-9_-]{22})(?:[/?#]|$)~', $value, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Handle (nové @meno) alebo staré vlastné meno kanála zo vstupu.
     */
    public static function handleFromInput(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || self::fromInput($value) !== null) {
            return null;
        }

        // Samotný handle, tak ako ho YouTube zobrazuje.
        if (preg_match('/^@([A-Za-z0-9._-]{3,100})$/', $value, $m)) {
            return $m[1];
        }

        if (str_contains($value, '/')) {
            return self::handleFromUrl($value);
        }

        // Meno kanála bez adresy a bez zavináča.
        return preg_match('/^[A-Za-z0-9._-]{3,100}$/', $value) === 1 ? $value : null;
    }

    /**
     * Doplní ID aj pre handle. `channels.list?forHandle` stojí jednu jednotku
     * kvóty, `search` až sto — preto je až poslednou možnosťou.
     *
     * @throws \Exception keď YouTube API odpovie chybou
     */
    public static function resolve(?string $value): ?string
    {
        if ($id = self::fromInput($value)) {
            return $id;
        }

        $handle = self::handleFromInput($value);

        if ($handle === null) {
            return null;
        }

        $lookups = [
            fn () => \Youtube::getChannelByHandle('@' . $handle, [], ['id']),
            fn () => \Youtube::getChannelByName($handle, [], ['id']),
            fn () => \Youtube::searchChannelByName($handle, 1, ['id', 'snippet']),
        ];

        foreach ($lookups as $lookup) {
            // Balík vracia pri prázdnej odpovedi false, nie objekt.
            $channel = $lookup();

            if (is_object($channel) && self::isId($channel->id ?? null)) {
                return $channel->id;
            }
        }

        return null;
    }

    private static function handleFromUrl(string $value): ?string
    {
        // Bez schémy dá parse_url celú adresu do cesty, takže by prvým
        // segmentom bola doména.
        $url = preg_match('~^https?://~i', $value) ? $value : 'https://' . ltrim($value, '/');
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host !== '' && ! str_contains($host, 'youtu')) {
            return null;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $segments = array_values(array_filter(explode('/', $path), 'strlen'));

        if ($segments === []) {
            return null;
        }

        // /@meno
        if (str_starts_with($segments[0], '@')) {
            return self::handleFromInput($segments[0]);
        }

        // /c/meno, /user/meno
        if (in_array($segments[0], ['c', 'user'], true)) {
            return isset($segments[1]) ? self::handleFromInput($segments[1]) : null;
        }

        // /meno (staré vlastné adresy)
        return in_array(strtolower($segments[0]), self::RESERVED, true)
            ? null
            : self::handleFromInput($segments[0]);
    }
}
