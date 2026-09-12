<?php

namespace App\Enums;

/**
 * Farebné ladenie oznamu. Ukladá sa názov ladenia, nie trieda — Tailwind
 * prehľadáva len `resources/**`, takže triedy zapísané v databáze (alebo tu
 * v PHP) by sa do zostaveného CSS nikdy nedostali. Preklad na triedy preto
 * býva v resources/views/components/announcements.blade.php.
 */
enum AnnouncementVariant: string
{
    case Info    = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger  = 'danger';
    case Neutral = 'neutral';

    public function label(): string
    {
        return match ($this) {
            self::Info    => 'Modrá — informácia',
            self::Success => 'Zelená — dobrá správa',
            self::Warning => 'Oranžová — upozornenie',
            self::Danger  => 'Červená — dôležité',
            self::Neutral => 'Tmavá — neutrálna',
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
