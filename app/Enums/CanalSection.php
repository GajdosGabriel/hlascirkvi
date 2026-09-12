<?php

namespace App\Enums;

/**
 * Kam smerujú nové videá kanála. Ukladá sa do `organizations.post_section`.
 *
 * Nahrádza updatery typu `listOfOrganization`: „živé vysielanie" (1)
 * znamenalo, že sa videá zverejnia hneď pri importe do nedeľných prenosov,
 * „default" (4) že pôjdu do buffera. Zvyšné zaradenie („vzdelávanie", 2)
 * neriadilo nič — bol to len štítok.
 */
enum CanalSection: string
{
    /**
     * Bežný kanál. Video po importe čaká v bufferi, kým ho publisher
     * nevypustí na úvodnú stránku (App\Services\Buffer).
     */
    case Front = 'front';

    /**
     * Kanál s prenosmi bohoslužieb. Video sa zverejní hneď pri importe,
     * v zozname nedeľných prenosov — čakať deň v bufferi na prenos, ktorý
     * práve beží, nemá zmysel.
     */
    case Live = 'live';

    public function label(): string
    {
        return match ($this) {
            self::Front => 'Úvodná stránka (cez buffer)',
            self::Live  => 'Nedeľné prenosy (hneď pri importe)',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Front => 'Nové videá čakajú vo fronte a publisher ich vypúšťa po jednom počas dňa.',
            self::Live  => 'Nové videá sa zverejnia okamžite v zozname prenosov.',
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
