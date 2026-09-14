<?php

namespace App\Enums;

/**
 * Liturgické obdobie. Počíta ho App\Services\Liturgy\LiturgicalCalendar,
 * ukladá sa do `liturgical_days.season`.
 *
 * Popisy zodpovedajú tomu, ako obdobie pomenúva liturgický kalendár KBS
 * (lc.kbs.sk) — príkaz liturgia:overit ich s ním porovnáva doslova.
 */
enum LiturgicalSeason: string
{
    case Advent = 'advent';

    /** Od Narodenia Pána po Krst Krista Pána vrátane. */
    case Christmas = 'christmas';

    case Ordinary = 'ordinary';

    /** Od Popolcovej stredy po Zelený štvrtok vrátane (tak to vedie KBS). */
    case Lent = 'lent';

    /** Veľký piatok až Veľkonočná nedeľa. */
    case Triduum = 'triduum';

    /** Od Veľkonočného pondelka po Zoslanie Ducha Svätého vrátane. */
    case Easter = 'easter';

    public function label(): string
    {
        return match ($this) {
            self::Advent => 'Adventné obdobie',
            self::Christmas => 'Vianočné obdobie',
            self::Ordinary => 'Cezročné obdobie',
            self::Lent => 'Pôstne obdobie',
            self::Triduum => 'Veľkonočné trojdnie',
            self::Easter => 'Veľkonočné obdobie',
        };
    }

    /** Pre riadok „24. týždeň v Cezročnom období" (lokál). */
    public function inLabel(): string
    {
        return match ($this) {
            self::Advent => 'v Adventnom období',
            self::Christmas => 'vo Vianočnom období',
            self::Ordinary => 'v Cezročnom období',
            self::Lent => 'v Pôstnom období',
            self::Triduum => 'vo Veľkonočnom trojdní',
            self::Easter => 'vo Veľkonočnom období',
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
