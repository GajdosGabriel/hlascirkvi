<?php

namespace App\Enums;

/** Jediný typ kanála pre správu aj verejné zoznamy. */
enum CanalIdentityMode: string
{
    case Personal = 'personal';
    case Organization = 'organization';
    case Pseudonymous = 'pseudonymous';

    public function label(): string
    {
        return match ($this) {
            self::Personal    => 'Osobný',
            self::Organization => 'Organizácia',
            self::Pseudonymous => 'Pseudonymný',
        };
    }

    /** Nadpis karty v bočnom paneli aj sekcie na stránke /osobnosti. */
    public function cardTitle(): string
    {
        return match ($this) {
            self::Personal    => 'Kresťanské osobnosti',
            self::Organization => 'Cirkvi a spoločenstvá',
            self::Pseudonymous => 'Pseudonymné kanály',
        };
    }

    /** Kotva sekcie na stránke /osobnosti, kam vedie odkaz z karty. */
    public function anchor(): string
    {
        return match ($this) {
            self::Personal    => 'osobnosti',
            self::Organization => 'spolocenstva',
            self::Pseudonymous => 'pseudonymne',
        };
    }

    public function showAllLabel(int $total): string
    {
        return match ($this) {
            self::Personal    => "Zobraziť všetkých {$total}",
            self::Organization => "Zobraziť všetky {$total}",
            self::Pseudonymous => "Zobraziť všetky {$total}",
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Personal    => 'components.icons.users',
            self::Organization => 'components.icons.canal',
            self::Pseudonymous => 'components.icons.users',
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
