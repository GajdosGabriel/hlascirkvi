<?php

namespace Tests\Unit;

use App\Services\Buffer\PublishPlan;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class PublishPlanTest extends TestCase
{
    protected array $config = [
        'daily' => ['min' => 3, 'max' => 18, 'inflow_days' => 7, 'keep_ahead_days' => 2, 'drain_days' => 30],
        'min_gap_minutes' => 25,
        'windows' => [
            ['from' => '07:05', 'to' => '09:10', 'weight' => 2],
            ['from' => '09:40', 'to' => '11:35', 'weight' => 2],
            ['from' => '12:05', 'to' => '13:45', 'weight' => 3],
            ['from' => '14:10', 'to' => '16:15', 'weight' => 2],
            ['from' => '16:50', 'to' => '19:05', 'weight' => 3],
            ['from' => '19:30', 'to' => '21:10', 'weight' => 2],
        ],
        'windows_sunday' => [
            ['from' => '12:30', 'to' => '14:40', 'weight' => 2],
            ['from' => '15:10', 'to' => '17:30', 'weight' => 3],
        ],
    ];

    protected function plan(): PublishPlan
    {
        return new PublishPlan($this->config);
    }

    public function testPlanIsStableForTheSameDay()
    {
        $day = CarbonImmutable::parse('2026-09-08');

        $this->assertEquals(
            $this->plan()->forDate($day, 120),
            $this->plan()->forDate($day->setTime(18, 42), 120)
        );
    }

    public function testEachDayGetsDifferentTimes()
    {
        $pondelok = $this->plan()->forDate(CarbonImmutable::parse('2026-09-07'), 120);
        $utorok = $this->plan()->forDate(CarbonImmutable::parse('2026-09-08'), 120);

        $this->assertNotEquals(
            array_map(fn ($slot) => $slot->format('H:i'), $pondelok),
            array_map(fn ($slot) => $slot->format('H:i'), $utorok)
        );
    }

    public function testSlotsKeepTheMinimumGapAndStayInsideTheDay()
    {
        foreach (range(1, 60) as $day) {
            $slots = $this->plan()->forDate(CarbonImmutable::parse('2026-01-01')->addDays($day), 400);

            $previous = null;

            foreach ($slots as $slot) {
                $this->assertGreaterThanOrEqual('07:05', $slot->format('H:i'));
                $this->assertLessThanOrEqual('21:10', $slot->format('H:i'));

                if ($previous !== null) {
                    $this->assertGreaterThanOrEqual(25, $previous->diffInMinutes($slot));
                }

                $previous = $slot;
            }
        }
    }

    public function testCountFollowsTheQueueSize()
    {
        $day = CarbonImmutable::parse('2026-09-08');

        $this->assertCount(0, $this->plan()->forDate($day, 0));
        // Menej čakajúcich, ako je denné minimum — nevymýšľa si sloty navyše.
        $this->assertCount(1, $this->plan()->forDate($day, 1));
        $this->assertLessThanOrEqual(18, count($this->plan()->forDate($day, 500, 13)));
        $this->assertGreaterThanOrEqual(3, count($this->plan()->forDate($day, 500)));
    }

    public function testShorterDayCarriesFewerPosts()
    {
        // Nedeľa má menej okien — do dňa sa toho zmestí menej aj pri rovnakom
        // fronte, presne ako by to spravil človek.
        $nedela = $this->plan()->forDate(CarbonImmutable::parse('2026-09-13'), 500, 13);
        $streda = $this->plan()->forDate(CarbonImmutable::parse('2026-09-09'), 500, 13);

        $this->assertLessThan(count($streda), count($nedela));
    }

    public function testCountKeepsUpWithTheInflow()
    {
        $day = CarbonImmutable::parse('2026-09-08');

        // Bez prítoku sa starý front len rozpúšťa (500 / 30 dní).
        $this->assertLessThanOrEqual(17, count($this->plan()->forDate($day, 500, 0)));

        // S prítokom 13 videí denne musí kvóta pokryť aspoň ten prítok, inak
        // by front rástol donekonečna.
        $this->assertGreaterThanOrEqual(13, count($this->plan()->forDate($day, 500, 13)));

        // Malý front a žiadny prítok — beží na dennom minime.
        $this->assertCount(3, $this->plan()->forDate($day, 20, 0));
    }
}
