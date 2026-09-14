<?php

namespace Tests\Unit\Liturgy;

use App\Enums\LiturgicalColor;
use App\Enums\LiturgicalSeason;
use App\Services\Liturgy\LiturgicalCalendar;
use PHPUnit\Framework\TestCase;

/**
 * Očakávané hodnoty sú overené proti liturgickému kalendáru KBS (lc.kbs.sk).
 */
class LiturgicalCalendarTest extends TestCase
{
    private LiturgicalCalendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendar = new LiturgicalCalendar;
    }

    public function test_vsedny_den_v_cezrocnom_obdobi(): void
    {
        $day = $this->calendar->for('2026-09-14');

        $this->assertSame(LiturgicalSeason::Ordinary, $day->season);
        $this->assertSame(24, $day->week);
        $this->assertSame(2026, $day->liturgicalYear);
        $this->assertSame('A', $day->sundayCycle);
        $this->assertSame(2, $day->weekdayCycle);
        $this->assertSame('A/II', $day->cycleLabel());
        $this->assertSame(4, $day->psalterWeek);
        $this->assertSame(LiturgicalColor::Green, $day->color);
        $this->assertSame('Pondelok 24. týždňa v Cezročnom období', $day->title);
        $this->assertSame(
            ['Rok A', 'cyklus II', '24. týždeň v Cezročnom období', '4. týždeň žaltára'],
            $day->contextParts()
        );
    }

    public function test_nedela_v_cezrocnom_obdobi_a_krista_krala(): void
    {
        $this->assertSame('25. nedeľa v Cezročnom období', $this->calendar->for('2026-09-20')->title);

        $christTheKing = $this->calendar->for('2026-11-22');
        $this->assertSame(34, $christTheKing->week);
        $this->assertTrue($christTheKing->isSunday());

        // Sobota pred ňou patrí ešte do 33. týždňa.
        $this->assertSame(33, $this->calendar->for('2026-11-21')->week);
    }

    public function test_prvou_adventnou_nedelou_zacina_novy_liturgicky_rok(): void
    {
        $saturday = $this->calendar->for('2026-11-28');
        $advent = $this->calendar->for('2026-11-29');

        $this->assertSame(LiturgicalSeason::Ordinary, $saturday->season);
        $this->assertSame('A/II', $saturday->cycleLabel());

        $this->assertSame(LiturgicalSeason::Advent, $advent->season);
        $this->assertSame(1, $advent->week);
        $this->assertSame(2027, $advent->liturgicalYear);
        $this->assertSame('B/I', $advent->cycleLabel());
        $this->assertSame(LiturgicalColor::Violet, $advent->color);
        $this->assertSame('1. adventná nedeľa', $advent->title);

        $this->assertSame(LiturgicalColor::Rose, $this->calendar->for('2026-12-13')->color);
    }

    public function test_vianocne_obdobie_konci_krstom_krista_pana(): void
    {
        $this->assertSame(LiturgicalSeason::Christmas, $this->calendar->for('2025-12-25')->season);
        $this->assertSame('2. nedeľa po narodení Pána', $this->calendar->for('2026-01-04')->title);
        $this->assertSame('Štvrtok vo Vianočnom období', $this->calendar->for('2026-01-08')->title);
        $this->assertSame('Krst Krista Pána', $this->calendar->for('2026-01-11')->title);
        $this->assertSame(LiturgicalSeason::Christmas, $this->calendar->for('2026-01-11')->season);

        $monday = $this->calendar->for('2026-01-12');
        $this->assertSame(LiturgicalSeason::Ordinary, $monday->season);
        $this->assertSame(1, $monday->week);
        $this->assertSame('2026-01-11', $this->calendar->baptismOfTheLord(2026)->format('Y-m-d'));
    }

    public function test_postne_obdobie_a_velkonocne_trojdnie(): void
    {
        $ash = $this->calendar->for('2026-02-18');
        $this->assertSame(LiturgicalSeason::Lent, $ash->season);
        $this->assertSame(0, $ash->week);
        $this->assertSame('Popolcová streda', $ash->title);
        $this->assertSame(4, $ash->psalterWeek);

        $this->assertSame('1. pôstna nedeľa', $this->calendar->for('2026-02-22')->title);
        $this->assertSame('Pondelok po 1. pôstnej nedeli', $this->calendar->for('2026-02-23')->title);
        $this->assertSame(LiturgicalColor::Rose, $this->calendar->for('2026-03-15')->color);
        $this->assertSame('Palmová (Kvetná) nedeľa – Nedeľa utrpenia Pána', $this->calendar->for('2026-03-29')->title);

        // Zelený štvrtok je podľa KBS ešte v Pôstnom období.
        $thursday = $this->calendar->for('2026-04-02');
        $this->assertSame(LiturgicalSeason::Lent, $thursday->season);
        $this->assertSame('Štvrtok Svätého týždňa – Zelený štvrtok', $thursday->title);

        $friday = $this->calendar->for('2026-04-03');
        $this->assertSame(LiturgicalSeason::Triduum, $friday->season);
        $this->assertSame(LiturgicalColor::Red, $friday->color);
        $this->assertSame('2023-04-07', $this->calendar->sameDayInCycle($friday, 3)?->format('Y-m-d'));
        $this->assertSame(LiturgicalSeason::Triduum, $this->calendar->for('2026-04-05')->season);
    }

    public function test_velkonocne_obdobie_konci_turicami(): void
    {
        $this->assertSame('Pondelok vo Veľkonočnej oktáve', $this->calendar->for('2026-04-06')->title);
        $this->assertSame('2. Veľkonočná nedeľa alebo Nedeľa Božieho milosrdenstva', $this->calendar->for('2026-04-12')->title);
        $this->assertSame('3. veľkonočná nedeľa', $this->calendar->for('2026-04-19')->title);
        $this->assertSame('Utorok po 2. veľkonočnej nedeli', $this->calendar->for('2026-04-14')->title);

        $pentecost = $this->calendar->for('2026-05-24');
        $this->assertSame(LiturgicalSeason::Easter, $pentecost->season);
        $this->assertSame('Zoslanie Ducha Svätého', $pentecost->title);
        $this->assertSame(LiturgicalColor::Red, $pentecost->color);

        $after = $this->calendar->for('2026-05-25');
        $this->assertSame(LiturgicalSeason::Ordinary, $after->season);
        $this->assertSame(8, $after->week);
    }

    public function test_ta_ista_nedela_o_tri_roky_skor_ma_to_iste_evanjelium(): void
    {
        $sunday = $this->calendar->for('2026-09-20');

        $this->assertSame('2023-09-24', $this->calendar->sameDayInCycle($sunday, 3)?->format('Y-m-d'));
        $this->assertSame('2020-09-20', $this->calendar->sameDayInCycle($sunday, 6)?->format('Y-m-d'));
        $this->assertSame('A', $this->calendar->for('2023-09-24')->sundayCycle);

        // Veľkonočné obdobie sa posúva s Veľkou nocou.
        $this->assertSame(
            '2023-05-28',
            $this->calendar->sameDayInCycle($this->calendar->for('2026-05-24'), 3)?->format('Y-m-d')
        );
    }

    public function test_odpocet_do_najblizsieho_sviatku(): void
    {
        $milestone = $this->calendar->nextMilestone('2026-09-14');

        $this->assertSame('1. adventná nedeľa', $milestone['title']);
        $this->assertSame('1. adventnej nedele', $milestone['until']);
        $this->assertSame(76, $milestone['days']);
        $this->assertSame('76 dní', $milestone['days_label']);
        $this->assertSame('3 dni', $this->calendar->nextMilestone('2026-11-26')['days_label']);
        $this->assertSame('1 deň', $this->calendar->nextMilestone('2026-11-28')['days_label']);

        $this->assertSame('Popolcová streda', $this->calendar->nextMilestone('2026-12-25')['title']);
    }
}
