<?php

namespace Tests\Unit\Liturgy;

use App\Services\Liturgy\Computus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ComputusTest extends TestCase
{
    /** @return array<int, array{int, string}> */
    public static function years(): array
    {
        return [
            [2000, '2000-04-23'],
            [2019, '2019-04-21'],
            [2024, '2024-03-31'],
            [2025, '2025-04-20'],
            [2026, '2026-04-05'],
            [2027, '2027-03-28'],
            // Najneskorší možný termín.
            [2038, '2038-04-25'],
        ];
    }

    #[DataProvider('years')]
    public function test_velka_noc_pripada_na_spravny_datum(int $year, string $expected): void
    {
        $this->assertSame($expected, Computus::easter($year)->format('Y-m-d'));
    }
}
