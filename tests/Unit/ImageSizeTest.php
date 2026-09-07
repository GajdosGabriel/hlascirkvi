<?php

namespace Tests\Unit;

use App\Enums\ImageSize;
use App\Services\Images\YoutubeThumbnail;
use PHPUnit\Framework\TestCase;

class ImageSizeTest extends TestCase
{
    /**
     * @return array<string, array{int, array<int, int>}>
     */
    public static function widthProvider(): array
    {
        return [
            'užší než najmenší variant' => [320, [1200]],
            'medzi malým a stredným' => [500, [1200, 400]],
            'presne najmenší variant' => [400, [1200]],
            'medzi stredným a veľkým' => [1000, [1200, 800, 400]],
            'širší než najväčší variant' => [1600, [1200, 800, 400]],
        ];
    }

    /**
     * @param array<int, int> $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('widthProvider')]
    public function test_vracia_len_varianty_ktore_predlohu_nezvacsuju(int $width, array $expected): void
    {
        $sizes = array_map(
            fn (ImageSize $size) => $size->value,
            ImageSize::forWidth($width)
        );

        $this->assertSame($expected, $sizes);
    }

    public function test_z_uzkej_predlohy_vznikne_prave_jeden_variant(): void
    {
        // Predloha 320 px sa nesmie rozpadnúť na tri rovnaké súbory.
        $this->assertCount(1, ImageSize::forWidth(320));
    }

    public function test_najvacsi_variant_je_vzdy_prvy(): void
    {
        // StoreImage zmenšuje postupne na jednom rastri, takže poradie
        // od najväčšieho po najmenší je podmienkou správnosti.
        $values = array_map(fn (ImageSize $s) => $s->value, ImageSize::cases());

        $sorted = $values;
        rsort($sorted);

        $this->assertSame($sorted, $values);
    }

    public function test_youtube_nahlad_berie_najvacsi_dostupny(): void
    {
        $thumbnails = json_decode(json_encode([
            'default' => ['url' => 'default.jpg'],
            'medium' => ['url' => 'mqdefault.jpg'],
            'high' => ['url' => 'hqdefault.jpg'],
            'maxres' => ['url' => 'maxresdefault.jpg'],
        ]));

        $this->assertSame('maxresdefault.jpg', YoutubeThumbnail::bestUrl($thumbnails));
    }

    public function test_youtube_nahlad_spadne_na_mensi_ked_vacsi_chyba(): void
    {
        $thumbnails = json_decode(json_encode([
            'default' => ['url' => 'default.jpg'],
            'medium' => ['url' => 'mqdefault.jpg'],
        ]));

        $this->assertSame('mqdefault.jpg', YoutubeThumbnail::bestUrl($thumbnails));
        $this->assertNull(YoutubeThumbnail::bestUrl(null));
        $this->assertNull(YoutubeThumbnail::bestUrl(json_decode('{}')));
    }
}
