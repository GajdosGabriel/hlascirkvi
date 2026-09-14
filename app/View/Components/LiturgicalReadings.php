<?php

namespace App\View\Components;

use App\Services\Liturgy\DailyReadings;
use App\Services\Liturgy\DayReadings;
use App\Services\Liturgy\LiturgicalCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Karta „Liturgia dňa" do bočného panela: <x-liturgical-readings />.
 *
 * Názov dňa s liturgickou farbou, cyklus čítaní, citácie, homílie
 * z archívu k tomu istému evanjeliu a odpočet do najbližšieho veľkého
 * sviatku. Vykreslí sa vždy — kalendár sa dá vypočítať aj bez KBS.
 */
class LiturgicalReadings extends Component
{
    public DayReadings $day;

    /** @var Collection<int, array<string, mixed>> */
    public Collection $homilies;

    /** @var array{title: string, until: string, date: CarbonImmutable, days: int, days_label: string} */
    public array $milestone;

    public function __construct(DailyReadings $readings, LiturgicalCalendar $calendar)
    {
        $this->day = $readings->forDate(now());
        $this->homilies = $readings->homilies($this->day->calendar);
        $this->milestone = $calendar->nextMilestone(now());
    }

    public function render()
    {
        return view('components.liturgical-readings');
    }
}
