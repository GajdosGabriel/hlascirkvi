<?php

namespace App\Enums;

/**
 * Cieľové šírky, v ktorých sa obrázok ukladá.
 *
 * Poradie prípadov je zámerne od najväčšieho: StoreImage ich prechádza v ňom
 * a každý ďalší krok len ďalej zmenšuje ten istý raster.
 */
enum ImageSize: int
{
    case Large = 1200;
    case Medium = 800;
    case Small = 400;

    /**
     * Varianty, ktoré má zmysel vyrobiť pre zdroj danej šírky. Nikdy
     * nezväčšujeme, takže z 320 px širokej predlohy vznikne jeden súbor,
     * nie tri rovnaké.
     *
     * @return array<int, self>
     */
    public static function forWidth(int $width): array
    {
        $sizes = [];

        foreach (self::cases() as $size) {
            if ($size->value >= $width && $sizes !== []) {
                continue;
            }

            $sizes[] = $size;
        }

        return $sizes;
    }
}
