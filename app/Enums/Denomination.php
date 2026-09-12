<?php

namespace App\Enums;

/**
 * Cirkevné zaradenie kanála. Ukladá sa do `organizations.denomination`.
 *
 * Do 9/2026 to bol riadok v `organization_updater` (updatery 12 a 13, typ
 * `denomination`) — hoci kanál mal vždy najviac jedno zaradenie, čo aj dáta
 * potvrdili: ani jeden nemal dve.
 */
enum Denomination: string
{
    case Catholic = 'catholic';
    case Evangelical = 'evangelical';

    public function label(): string
    {
        return match ($this) {
            self::Catholic    => 'Katolícka',
            self::Evangelical => 'Protestantská',
        };
    }

    /** Ikona k štítku vo výpisoch kanálov. */
    public function icon(): string
    {
        return match ($this) {
            self::Catholic    => 'fab fa-korvue',
            self::Evangelical => 'fab fa-product-hunt',
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
