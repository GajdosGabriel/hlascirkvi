<?php

namespace App\Enums;

/** Jediný typ kanála pre správu aj verejné zoznamy. */
enum CanalType: string
{
    case Personal = 'personal';
    case Organization = 'organization';

    public function label(): string
    {
        return match ($this) {
            self::Personal    => 'Osobný',
            self::Organization => 'Organizácia',
        };
    }

    /** Nadpis karty v bočnom paneli aj sekcie na stránke /osobnosti. */
    public function cardTitle(): string
    {
        return match ($this) {
            self::Personal    => 'Kresťanské osobnosti',
            self::Organization => 'Cirkvi a spoločenstvá',
        };
    }

    /** Kotva sekcie na stránke /osobnosti, kam vedie odkaz z karty. */
    public function anchor(): string
    {
        return match ($this) {
            self::Personal    => 'osobnosti',
            self::Organization => 'spolocenstva',
        };
    }

    public function showAllLabel(int $total): string
    {
        return match ($this) {
            self::Personal    => "Zobraziť všetkých {$total}",
            self::Organization => "Zobraziť všetky {$total}",
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Personal    => 'components.icons.users',
            self::Organization => 'components.icons.canal',
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
