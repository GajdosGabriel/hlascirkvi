<?php

namespace App\Services\Youtube;

/**
 * ID videa z odpovede YouTube API. `search.list` vracia ID v obale
 * `id->videoId`, ale aj pri `type=video` sa medzi výsledkami objaví
 * položka bez neho (kanál, playlist, zmazané video). Pôvodný priamy
 * prístup na `$video->id->videoId` vtedy skončil na „Undefined property:
 * stdClass::$videoId" a výnimka zhodila import zvyšných videí toho
 * kanála (v produkcii kanál 465 Komunita Blahoslavenstiev).
 *
 * Ostatné tvary (`activities.list`, `playlistItems.list`) sú tu preto,
 * aby volajúci nemusel riešiť, z ktorého endpointu položka prišla.
 */
class VideoId
{
    /** ID videa: 11 znakov base64url. */
    public const PATTERN = '/^[A-Za-z0-9_-]{11}$/';

    public static function isId(?string $value): bool
    {
        return is_string($value) && preg_match(self::PATTERN, trim($value)) === 1;
    }

    /**
     * ID videa z položky odpovede, alebo null keď položka video neopisuje.
     *
     * @param object|array|null $item
     */
    public static function from($item): ?string
    {
        $item = is_array($item) ? (object) $item : $item;

        if (! is_object($item)) {
            return null;
        }

        $candidates = [
            // search.list
            $item->id->videoId ?? null,
            // videos.list — ID je priamo reťazec
            is_string($item->id ?? null) ? $item->id : null,
            // activities.list
            $item->contentDetails->upload->videoId ?? null,
            $item->contentDetails->playlistItem->resourceId->videoId ?? null,
            // playlistItems.list
            $item->snippet->resourceId->videoId ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (self::isId($candidate)) {
                return trim($candidate);
            }
        }

        return null;
    }
}
