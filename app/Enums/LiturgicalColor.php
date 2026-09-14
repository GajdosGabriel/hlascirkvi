<?php

namespace App\Enums;

/**
 * Liturgická farba dňa. Pri fériách a nedeliach ju určí kalendár sám,
 * pri sviatkoch svätých ju preberáme z KBS.
 *
 * Farba sa na webe kreslí inline štýlom z hex() — Tailwind prehľadáva len
 * šablóny, triedy vrátené z PHP by do CSS nevygeneroval.
 */
enum LiturgicalColor: string
{
    case Green = 'green';
    case Violet = 'violet';
    case White = 'white';
    case Red = 'red';
    case Rose = 'rose';

    public function label(): string
    {
        return match ($this) {
            self::Green => 'zelená',
            self::Violet => 'fialová',
            self::White => 'biela',
            self::Red => 'červená',
            self::Rose => 'ružová',
        };
    }

    /** Biela by na bielej karte nebola vidieť — kreslí sa zlatou, ako v misáli. */
    public function hex(): string
    {
        return match ($this) {
            self::Green => '#15803d',
            self::Violet => '#6d28d9',
            self::White => '#c9a227',
            self::Red => '#b91c1c',
            self::Rose => '#db2777',
        };
    }

    /** Z titulku „Liturgická farba: Červená" na stránke KBS. */
    public static function fromKbs(string $text): ?self
    {
        $text = mb_strtolower($text);

        return match (true) {
            str_contains($text, 'zelen') => self::Green,
            str_contains($text, 'fialov') => self::Violet,
            str_contains($text, 'biel') => self::White,
            str_contains($text, 'červen') => self::Red,
            str_contains($text, 'ružov') => self::Rose,
            default => null,
        };
    }

    /** @return array<int, self> */
    public static function options(): array
    {
        return self::cases();
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
