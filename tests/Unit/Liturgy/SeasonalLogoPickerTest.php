<?php

namespace Tests\Unit\Liturgy;

use App\Enums\SeasonalLogo;
use App\Services\Liturgy\LiturgicalCalendar;
use App\Services\Liturgy\SeasonalLogoPicker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Veľká noc 2026 je 5. 4., Turíce 24. 5., 1. adventná nedeľa 29. 11. 2026,
 * Popolcová streda 2027 je 10. 2.
 */
class SeasonalLogoPickerTest extends TestCase
{
    /** @return array<string, array{0: string, 1: SeasonalLogo}> */
    public static function days(): array
    {
        return [
            'jeseň' => ['2026-10-24', SeasonalLogo::Autumn],
            'koniec novembra pred adventom' => ['2026-11-28', SeasonalLogo::Autumn],
            '1. adventná nedeľa' => ['2026-11-29', SeasonalLogo::Advent],
            'Štedrý deň' => ['2026-12-24', SeasonalLogo::Christmas],
            'Krst Krista Pána' => ['2027-01-10', SeasonalLogo::Christmas],
            'január po Vianociach' => ['2027-01-11', SeasonalLogo::Snowy],
            'február pred Pôstom' => ['2027-02-09', SeasonalLogo::Snowman],
            'Popolcová streda' => ['2027-02-10', SeasonalLogo::Plain],
            'Zelený štvrtok' => ['2026-04-02', SeasonalLogo::Plain],
            'Veľký piatok' => ['2026-04-03', SeasonalLogo::Plain],
            'Biela sobota' => ['2026-04-04', SeasonalLogo::Plain],
            'Veľkonočná nedeľa' => ['2026-04-05', SeasonalLogo::Easter],
            'Nedeľa Božieho milosrdenstva' => ['2026-04-12', SeasonalLogo::Easter],
            'po Veľkonočnej oktáve' => ['2026-04-13', SeasonalLogo::Spring],
            'Nanebovstúpenie Pána' => ['2026-05-14', SeasonalLogo::Spring],
            'začiatok novény k Duchu Svätému' => ['2026-05-15', SeasonalLogo::Pentecost],
            'Zoslanie Ducha Svätého' => ['2026-05-24', SeasonalLogo::Pentecost],
            'po Turíciach' => ['2026-05-25', SeasonalLogo::Spring],
            'leto' => ['2026-07-15', SeasonalLogo::Summer],
            'advent začína ešte v novembri' => ['2025-11-30', SeasonalLogo::Advent],
            'december pred adventom' => ['2030-11-30', SeasonalLogo::Autumn],
        ];
    }

    #[DataProvider('days')]
    public function test_variant_podla_dna(string $date, SeasonalLogo $expected): void
    {
        $this->assertSame($expected, $this->picker()->for($date)['logo']);
    }

    public function test_sviecky_podla_adventneho_tyzdna(): void
    {
        $this->assertSame(1, $this->picker()->for('2026-11-29')['candles']);
        $this->assertSame(1, $this->picker()->for('2026-12-05')['candles']);
        $this->assertSame(3, $this->picker()->for('2026-12-13')['candles']);
        $this->assertSame(4, $this->picker()->for('2026-12-23')['candles']);
        $this->assertSame(0, $this->picker()->for('2026-12-24')['candles']);
    }

    private function picker(): SeasonalLogoPicker
    {
        return new SeasonalLogoPicker(new LiturgicalCalendar);
    }
}
