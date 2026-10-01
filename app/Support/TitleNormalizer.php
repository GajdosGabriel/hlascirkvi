<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Titulok z YouTube importu do podoby vhodnej pre výpis, <title> a zdieľanie:
 * bez emoji, bez VERZÁLOK a bez výzvy z popisu videa namiesto názvu.
 *
 * Stĺpec `title` je v kódovaní utf8 (3 bajty), takže emoji z mimo základnej
 * roviny sa v ňom ukladali ako „?“ — preto sa čistia aj ich zvyšky.
 */
class TitleNormalizer
{
    private const EMOJI = '/[\x{1F000}-\x{1FFFF}\x{2190}-\x{21FF}\x{2300}-\x{23FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}\x{200D}\x{20E3}\x{E0000}-\x{E007F}]/u';

    /** Skratky, ktoré sa po zmene verzálok majú ponechať. */
    private const ACRONYMS = ['SK', 'CZ', 'DE', 'HU', 'PL', 'TV', 'FM', 'USA', 'EU', 'OSN', 'SVD', 'SJ', 'OSB', 'OFM', 'OP', 'KBS', 'ECAV', 'RKC', 'GKC', 'CB', 'AC', 'KS', 'KN', 'AI', 'CD', 'DVD', 'LIVE', 'EVS', 'TKKBS', 'TV LUX'];

    private const STOPWORDS = ['a', 'i', 'v', 'vo', 'z', 'zo', 'k', 'ku', 's', 'so', 'u', 'o', 'na', 'do', 'od', 'po', 'pre', 'pri', 'za', 'sa', 'aj', 'ale', 'alebo', 'či', 'ako', 'že', 'to'];

    public static function clean(string $title): string
    {
        // Hashtagy z popisu videa (`#godzone`); `#65` ostáva, je to číslo dielu.
        $title = preg_replace('/#\p{L}[\p{L}\p{N}_]*/u', ' ', $title);
        $title = preg_replace(self::EMOJI, ' ', $title);

        // Zvyšky emoji: súvislé „?“ (aj so spojkou ZWJ), alebo „?“ na začiatku.
        $title = preg_replace('/(?<=\p{L})\?(?:\s+\?)+/u', '?', $title);
        $title = preg_replace('/\?{2,}/u', ' ', $title);
        $title = preg_replace('/(?:\s\?)+(?=\s|$)/u', '', $title);
        $title = preg_replace('/^\s*\?+\s*/u', '', $title);

        $title = preg_replace('/\s+/u', ' ', $title);
        // Po odstránení značky ostávajú visiace oddeľovače: `13.4./ | Meno`, `Názov /`.
        $title = preg_replace('/\s*\/\s*\|/u', ' |', $title);
        $title = trim($title, " \t-–—•|:,/");

        return static::deshout($title);
    }

    /** Titulok z výzvy v popise videa (napr. „Všetky podcasty nájdeš na našom YOUTUBE“). */
    public static function isPlaceholder(string $title): bool
    {
        return (bool) preg_match('/^všetky podcasty nájdeš na našom youtube/iu', $title);
    }

    public static function fallback(string $canalTitle, $date): string
    {
        return trim($canalTitle . ' – video z ' . Carbon::parse($date)->format('j. n. Y'));
    }

    private static function deshout(string $title): string
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $title);

        if (mb_strlen($letters) < 8
            || mb_strlen(preg_replace('/[^\p{Lu}]/u', '', $letters)) / mb_strlen($letters) < 0.8) {
            return $title;
        }

        $first = true;

        return preg_replace_callback('/[\p{L}\p{N}\.\'’]+/u', function ($m) use (&$first) {
            $word = $m[0];

            if (! preg_match('/\p{L}/u', $word)) {
                return $word;
            }

            $bare = rtrim($word, '.');
            $lead = $first;
            $first = false;

            if (in_array($bare, self::ACRONYMS, true)
                || preg_match('/^[IVXL]{2,}$/', $bare)
                || preg_match('/^(\p{Lu}\.)+$/u', $word)) {
                return $word;
            }

            $lower = mb_strtolower($word);

            if (! $lead && in_array(rtrim($lower, '.'), self::STOPWORDS, true)) {
                return $lower;
            }

            return mb_strtoupper(mb_substr($lower, 0, 1)) . mb_substr($lower, 1);
        }, $title);
    }
}
