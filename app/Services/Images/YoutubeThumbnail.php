<?php

namespace App\Services\Images;

/**
 * YouTube vracia v snippete len tie náhľady, ktoré k videu naozaj existujú.
 * Doteraz sa bral natvrdo „medium" (320x180) a ten sa ešte roztiahol na 360 px,
 * takže obrázok nad článkom bol rozmazaný. Berieme najväčší dostupný – je
 * v tej istej odpovedi, nestojí to ďalšie volanie API.
 */
final class YoutubeThumbnail
{
    /**
     * Od najväčšieho po najmenší: 1280x720, 640x480, 480x360, 320x180, 120x90.
     */
    private const PREFERENCE = ['maxres', 'standard', 'high', 'medium', 'default'];

    public static function bestUrl(mixed $thumbnails): ?string
    {
        if (! is_object($thumbnails)) {
            return null;
        }

        foreach (self::PREFERENCE as $key) {
            if (! empty($thumbnails->{$key}->url)) {
                return (string) $thumbnails->{$key}->url;
            }
        }

        return null;
    }
}
