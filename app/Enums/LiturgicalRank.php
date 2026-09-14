<?php

namespace App\Enums;

/**
 * Stupeň slávenia dňa podľa slovenského liturgického kalendára.
 */
enum LiturgicalRank: string
{
    case Solemnity = 'solemnity';
    case Feast = 'feast';
    case Memorial = 'memorial';
    case OptionalMemorial = 'optional_memorial';
    case Sunday = 'sunday';
    case Feria = 'feria';

    public function label(): string
    {
        return match ($this) {
            self::Solemnity => 'slávnosť',
            self::Feast => 'sviatok',
            self::Memorial => 'spomienka',
            self::OptionalMemorial => 'ľubovoľná spomienka',
            self::Sunday => 'nedeľa',
            self::Feria => 'féria',
        };
    }

    /**
     * Z textu v zátvorke pri názve dňa na stránke KBS („sviatok",
     * „slávnosť s oktávou"…). Null, keď tam stojí niečo iné — napríklad
     * na Popolcovú stredu poznámka o pôste.
     */
    public static function fromKbs(string $text): ?self
    {
        $text = mb_strtolower($text);

        return match (true) {
            str_contains($text, 'slávnosť') => self::Solemnity,
            str_contains($text, 'ľubovoľná spomienka') => self::OptionalMemorial,
            str_contains($text, 'spomienka') => self::Memorial,
            str_contains($text, 'sviatok') => self::Feast,
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
