<?php

namespace App\Enums;

/**
 * Do ktorého výpisu príspevok patrí. Ukladá sa do `posts.section`.
 *
 * Do 9/2026 to bol riadok v `post_updater` s updaterom 15, 16 alebo 17 —
 * a ten istý riadok zároveň znamenal „príspevok je zverejnený". Dve veci
 * v jednom zázname: príspevok bez updatera čakal v bufferi, ale nedalo sa
 * povedať, kam raz pôjde, a zaradenie sa nedalo zmeniť bez toho, aby sa
 * nedotklo zverejnenia. Stav dnes nesie `published_at`, zaradenie tento
 * stĺpec.
 *
 * Dáta pri prevode potvrdili, že väzba nikdy nebola M:N: ani jeden
 * z 41 428 príspevkov nemal viac než jeden updater.
 */
enum PostSection: string
{
    /** Úvodná stránka. Sem ide všetko, čo prejde bufferom. */
    case Front = 'front';

    /** Nedeľné prenosy (/online-prenosy) — zverejňujú sa hneď pri importe. */
    case Live = 'live';

    /** Konferencie a púte (/konferencie-a-pute). */
    case Seminar = 'seminar';

    public function label(): string
    {
        return match ($this) {
            self::Front   => 'Úvodná stránka',
            self::Live    => 'Nedeľné prenosy',
            self::Seminar => 'Konferencie a púte',
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
