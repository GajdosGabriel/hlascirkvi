<?php

namespace App\Services\Youtube;

/**
 * To isté ako pri [ChannelId] pre `organizations.youtube_playlist` — z adresy
 * playlistu vytiahne parameter `list`. ID playlistu sa nedá resolvovať z mena,
 * takže tu žiadny dopyt na API nie je.
 */
class PlaylistId
{
    /**
     * PL… (vlastné playlisty), UU…/FL…/LL… (automatické), OLAK5uy_… (albumy).
     */
    public const PATTERN = '/^(?:PL|UU|FL|LL|RD|OLAK5uy_)[A-Za-z0-9_-]{10,}$/';

    public static function isId(?string $value): bool
    {
        return is_string($value) && preg_match(self::PATTERN, trim($value)) === 1;
    }

    public static function fromInput(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (self::isId($value)) {
            return $value;
        }

        // …/playlist?list=PL… aj …/watch?v=…&list=PL…
        if (preg_match('~[?&]list=([A-Za-z0-9_-]+)~', $value, $m) && self::isId($m[1])) {
            return $m[1];
        }

        return null;
    }
}
