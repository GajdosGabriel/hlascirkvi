<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Liturgy\DailyReadings;
use App\Services\Liturgy\LiturgicalCalendar;
use Carbon\CarbonImmutable;

/**
 * Liturgické čítania na jeden deň: /citania (dnes) a /citania/RRRR-MM-DD.
 *
 * Rozsah je rok dozadu aj dopredu — ďalej by prehliadanie (a roboty
 * vyhľadávačov) zbytočne ťahali stránky z KBS.
 */
class ReadingsController extends Controller
{
    public function show(DailyReadings $readings, LiturgicalCalendar $calendar, ?string $datum = null)
    {
        $today = CarbonImmutable::today();
        $date = $today;

        if ($datum !== null) {
            try {
                $date = CarbonImmutable::createFromFormat('!Y-m-d', $datum);
            } catch (\Throwable) {
                abort(404);
            }

            // createFromFormat by 2026-02-30 potichu posunul na 2. marec.
            abort_if(! $date || $date->format('Y-m-d') !== $datum, 404);
            abort_if($date->lt($today->subYear()) || $date->gt($today->addYear()), 404);
        }

        $day = $readings->forDate($date);

        // Týždeň okolo dňa, nech sa dá preskočiť na nedeľu či sviatok.
        $week = collect(range(-3, 3))->map(fn (int $offset) => $calendar->for($date->addDays($offset)));

        return view('readings.show', [
            'day' => $day,
            'week' => $week,
            'homilies' => $readings->homilies($day->calendar),
            'milestone' => $calendar->nextMilestone($date),
            'prev' => $date->subDay()->gte($today->subYear()) ? $date->subDay() : null,
            'next' => $date->addDay()->lte($today->addYear()) ? $date->addDay() : null,
            'isToday' => $date->isSameDay($today),
            'fullTexts' => (bool) config('liturgy.full_texts'),
        ]);
    }
}
