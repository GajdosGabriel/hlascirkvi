<?php

namespace App\Enums;

/**
 * Kto za kanálom stojí — človek, alebo cirkev či spoločenstvo. Ukladá sa do
 * `canals.kind`.
 *
 * Predný zoznam sa volal „Kresťanské osobnosti", ale popri kazateľoch v ňom
 * stáli aj ECAV, TKKBS či TV LUX. Typ ich rozdeľuje na dve karty; kanál bez
 * typu sa v prednom zozname na webe neukáže, kým ho správca nezaradí.
 */
enum CanalKind: string
{
    case Person = 'person';
    case Community = 'community';

    public function label(): string
    {
        return match ($this) {
            self::Person    => 'Osobnosť',
            self::Community => 'Cirkev / spoločenstvo',
        };
    }

    /** Nadpis karty v bočnom paneli aj sekcie na stránke /osobnosti. */
    public function cardTitle(): string
    {
        return match ($this) {
            self::Person    => 'Kresťanské osobnosti',
            self::Community => 'Cirkvi a spoločenstvá',
        };
    }

    /** Kotva sekcie na stránke /osobnosti, kam vedie odkaz z karty. */
    public function anchor(): string
    {
        return match ($this) {
            self::Person    => 'osobnosti',
            self::Community => 'spolocenstva',
        };
    }

    public function showAllLabel(int $total): string
    {
        return match ($this) {
            self::Person    => "Zobraziť všetkých {$total}",
            self::Community => "Zobraziť všetky {$total}",
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Person    => 'components.icons.users',
            self::Community => 'components.icons.canal',
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
