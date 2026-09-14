<?php

namespace App\Services\Liturgy;

use App\Enums\LiturgicalColor;
use App\Enums\LiturgicalSeason;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Všeobecný rímsky kalendár tak, ako sa slávi na Slovensku — len jeho
 * kostra: obdobia, týždne, cykly čítaní, týždeň žaltára a farba férií.
 *
 *  - Liturgický rok začína 1. adventnou nedeľou (4. nedeľa pred 25. 12.)
 *    a nesie číslo roka, v ktorom končí.
 *  - Nedeľný cyklus: rok % 3 → 1 = A, 2 = B, 0 = C. Feriálny: nepárny = I.
 *  - Zjavenie Pána je na Slovensku 6. januára, Krst Krista Pána teda
 *    nasledujúcu nedeľu.
 *  - Cezročné obdobie po Turíciach sa čísluje odzadu — 34. týždeň je vždy
 *    ten pred Adventom, preto sa niektoré týždne v danom roku preskočia.
 *
 * Sviatky svätých a presúvanie slávností podľa tabuľky precedencie tu nie
 * sú: tie berieme z KBS. Príkaz liturgia:overit porovnáva výpočet s KBS.
 *
 * Interne sa počíta v UTC, aby prechod na letný čas nepokazil počet dní.
 */
final class LiturgicalCalendar
{
    private const WEEKDAYS = ['Nedeľa', 'Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota'];

    public function for(CarbonInterface|string $date): LiturgicalDate
    {
        $day = $this->normalize($date);
        $year = $this->liturgicalYearOf($day);

        [$season, $week] = $this->seasonAndWeek($day, $year);

        return new LiturgicalDate(
            date: CarbonImmutable::parse($day->format('Y-m-d')),
            liturgicalYear: $year,
            season: $season,
            week: $week,
            sundayCycle: ['C', 'A', 'B'][$year % 3],
            weekdayCycle: $year % 2 === 1 ? 1 : 2,
            psalterWeek: $this->psalterWeek($season, $week),
            color: $this->color($season, $week, $day),
            title: $this->title($season, $week, $day),
        );
    }

    /** 1. adventná nedeľa v danom kalendárnom roku. */
    public function adventStart(int $year): CarbonImmutable
    {
        $christmas = $this->ymd($year, 12, 25);
        $dow = $christmas->dayOfWeek;

        // 4. adventná nedeľa je posledná nedeľa pred Vianocami (ak sú Vianoce
        // v nedeľu, tak o týždeň skôr), prvá je o tri týždne skôr.
        return $christmas->subDays($dow === 0 ? 7 : $dow)->subWeeks(3);
    }

    /** Krst Krista Pána — nedeľa po 6. januári. */
    public function baptismOfTheLord(int $year): CarbonImmutable
    {
        $epiphany = $this->ymd($year, 1, 6);

        return $epiphany->addDays(7 - $epiphany->dayOfWeek);
    }

    public function liturgicalYear(CarbonInterface|string $date): int
    {
        return $this->liturgicalYearOf($this->normalize($date));
    }

    /**
     * Ten istý deň (obdobie, týždeň, deň v týždni) o `$yearsBack` liturgických
     * rokov skôr. Nedeľné čítania sa opakujú po troch rokoch, takže homília
     * spred 3, 6 či 9 rokov bola na to isté evanjelium.
     *
     * Null, keď taký deň v danom roku nebol (preskočený týždeň Cezročného
     * obdobia).
     */
    public function sameDayInCycle(LiturgicalDate $day, int $yearsBack): ?CarbonImmutable
    {
        $year = $day->liturgicalYear - $yearsBack;
        $dow = $day->date->dayOfWeek;
        $easter = Computus::easter($year);

        $candidates = match ($day->season) {
            LiturgicalSeason::Advent => [
                $this->adventStart($year - 1)->addDays(($day->week - 1) * 7 + $dow),
            ],
            // Vianoce sú viazané na dátum, nie na týždeň.
            LiturgicalSeason::Christmas => [
                $this->ymd($day->date->year - $yearsBack, $day->date->month, $day->date->day),
            ],
            // Týždeň s daným číslom môže v inom roku padnúť pred Pôst aj po Turíce.
            LiturgicalSeason::Ordinary => [
                $this->baptismOfTheLord($year)->addDays(($day->week - 1) * 7 + $dow),
                $this->adventStart($year)->subDays((35 - $day->week) * 7)->addDays($dow),
            ],
            LiturgicalSeason::Lent => [
                $day->week === 0
                    ? $easter->subDays(46)->addDays($dow - 3)
                    : $easter->subDays(42)->addDays(($day->week - 1) * 7 + $dow),
            ],
            LiturgicalSeason::Triduum => [
                $easter->subDays(3 - $day->week),
            ],
            LiturgicalSeason::Easter => [
                $easter->addDays(($day->week - 1) * 7 + $dow),
            ],
        };

        foreach ($candidates as $candidate) {
            $check = $this->for($candidate);

            if ($check->season !== $day->season) {
                continue;
            }

            if ($day->season === LiturgicalSeason::Christmas
                || ($check->week === $day->week && $check->date->dayOfWeek === $dow)) {
                return $check->date;
            }
        }

        return null;
    }

    /**
     * Najbližší veľký míľnik liturgického roka po danom dni — pre odpočet
     * v module („Do 1. adventnej nedele zostáva 76 dní").
     *
     * `until` je názov v genitíve („Do Veľkej noci…").
     *
     * @return array{title: string, until: string, date: CarbonImmutable, days: int, days_label: string}
     */
    public function nextMilestone(CarbonInterface|string $date): array
    {
        $day = $this->normalize($date);
        $events = [];

        foreach ([$day->year, $day->year + 1] as $year) {
            $easter = Computus::easter($year);

            $events[] = ['Popolcová streda', 'Popolcovej stredy', $easter->subDays(46)];
            $events[] = ['Veľká noc', 'Veľkej noci', $easter];
            $events[] = ['Zoslanie Ducha Svätého', 'Zoslania Ducha Svätého', $easter->addDays(49)];
            $events[] = ['1. adventná nedeľa', '1. adventnej nedele', $this->adventStart($year)];
            $events[] = ['Narodenie Pána', 'Narodenia Pána', $this->ymd($year, 12, 25)];
        }

        $events = array_filter($events, fn (array $event) => $event[2]->gt($day));
        usort($events, fn (array $a, array $b) => $a[2] <=> $b[2]);

        [$title, $until, $when] = $events[array_key_first($events)];

        return [
            'title' => $title,
            'until' => $until,
            'days_label' => $this->daysLabel($this->days($day, $when)),
            'date' => CarbonImmutable::parse($when->format('Y-m-d')),
            'days' => $this->days($day, $when),
        ];
    }

    /** @return array{0: LiturgicalSeason, 1: int} */
    private function seasonAndWeek(CarbonImmutable $day, int $year): array
    {
        $easter = Computus::easter($year);
        $christmas = $this->ymd($year - 1, 12, 25);
        $baptism = $this->baptismOfTheLord($year);
        $ash = $easter->subDays(46);
        $lent1 = $easter->subDays(42);
        // KBS počíta Zelený štvrtok ešte do Pôstu — Trojdnie začína večernou omšou.
        $triduum = $easter->subDays(2);
        $pentecost = $easter->addDays(49);

        return match (true) {
            $day->lt($christmas) => [
                LiturgicalSeason::Advent,
                intdiv($this->days($this->adventStart($year - 1), $day), 7) + 1,
            ],
            $day->lte($baptism) => [LiturgicalSeason::Christmas, 0],
            $day->lt($ash) => [
                LiturgicalSeason::Ordinary,
                intdiv($this->days($baptism, $day), 7) + 1,
            ],
            $day->lt($triduum) => [
                LiturgicalSeason::Lent,
                $day->lt($lent1) ? 0 : intdiv($this->days($lent1, $day), 7) + 1,
            ],
            $day->lte($easter) => [LiturgicalSeason::Triduum, $this->days($triduum, $day) + 1],
            $day->lte($pentecost) => [
                LiturgicalSeason::Easter,
                intdiv($this->days($easter, $day), 7) + 1,
            ],
            default => [
                LiturgicalSeason::Ordinary,
                34 - intdiv($this->days($day, $this->adventStart($year)) - 1, 7),
            ],
        };
    }

    private function psalterWeek(LiturgicalSeason $season, int $week): ?int
    {
        return match ($season) {
            // Po Popolcovej strede (týždeň 0) sa berie 4. týždeň žaltára.
            LiturgicalSeason::Advent,
            LiturgicalSeason::Ordinary,
            LiturgicalSeason::Lent => (($week + 3) % 4) + 1,
            // Veľkonočná oktáva má vlastné časti.
            LiturgicalSeason::Easter => $week >= 2 ? (($week + 3) % 4) + 1 : null,
            default => null,
        };
    }

    private function color(LiturgicalSeason $season, int $week, CarbonImmutable $day): LiturgicalColor
    {
        $sunday = $day->dayOfWeek === 0;

        return match ($season) {
            LiturgicalSeason::Advent => $sunday && $week === 3 ? LiturgicalColor::Rose : LiturgicalColor::Violet,
            LiturgicalSeason::Christmas => LiturgicalColor::White,
            LiturgicalSeason::Ordinary => LiturgicalColor::Green,
            LiturgicalSeason::Lent => match (true) {
                $sunday && $week === 4 => LiturgicalColor::Rose,
                $sunday && $week === 6 => LiturgicalColor::Red,
                $week === 6 && $day->dayOfWeek === 4 => LiturgicalColor::White,
                default => LiturgicalColor::Violet,
            },
            LiturgicalSeason::Triduum => $week === 1 ? LiturgicalColor::Red : LiturgicalColor::White,
            LiturgicalSeason::Easter => $week === 8 ? LiturgicalColor::Red : LiturgicalColor::White,
        };
    }

    /** Názov dňa bez sviatku svätého — náhrada, kým nie sú dáta z KBS. */
    private function title(LiturgicalSeason $season, int $week, CarbonImmutable $day): string
    {
        $weekday = self::WEEKDAYS[$day->dayOfWeek];
        $sunday = $day->dayOfWeek === 0;

        // Znenie názvov je zosúladené s kalendárom KBS (liturgia:overit).
        return match ($season) {
            LiturgicalSeason::Advent => match (true) {
                $sunday && $week === 3 => '3. adventná nedeľa (Nedeľa Gaudete)',
                $sunday => "{$week}. adventná nedeľa",
                default => "{$weekday} po {$week}. adventnej nedeli",
            },
            LiturgicalSeason::Christmas => $this->christmasTitle($day),
            LiturgicalSeason::Ordinary => match (true) {
                $sunday && $week === 3 => '3. nedeľa v Cezročnom období (Nedeľa Božieho slova)',
                $sunday => "{$week}. nedeľa v Cezročnom období",
                default => "{$weekday} {$week}. týždňa v Cezročnom období",
            },
            LiturgicalSeason::Lent => match (true) {
                $week === 0 => $day->dayOfWeek === 3 ? 'Popolcová streda' : "{$weekday} po Popolcovej strede",
                $week === 6 && $sunday => 'Palmová (Kvetná) nedeľa – Nedeľa utrpenia Pána',
                $week === 6 && $day->dayOfWeek === 4 => 'Štvrtok Svätého týždňa – Zelený štvrtok',
                $week === 6 => "{$weekday} Svätého týždňa",
                $sunday && $week === 4 => '4. pôstna nedeľa (nedeľa Laetare)',
                $sunday => "{$week}. pôstna nedeľa",
                default => "{$weekday} po {$week}. pôstnej nedeli",
            },
            LiturgicalSeason::Triduum => ['Veľký piatok', 'Biela sobota', 'Veľkonočná nedeľa'][$week - 1],
            LiturgicalSeason::Easter => match (true) {
                $week === 8 => 'Zoslanie Ducha Svätého',
                $week === 1 => "{$weekday} vo Veľkonočnej oktáve",
                $sunday && $week === 2 => '2. Veľkonočná nedeľa alebo Nedeľa Božieho milosrdenstva',
                $sunday => "{$week}. veľkonočná nedeľa",
                default => "{$weekday} po {$week}. veľkonočnej nedeli",
            },
        };
    }

    private function christmasTitle(CarbonImmutable $day): string
    {
        $weekday = self::WEEKDAYS[$day->dayOfWeek];

        return match (true) {
            $day->month === 12 && $day->day === 25 => 'Narodenie Pána',
            $day->month === 1 && $day->day === 1 => 'Panna Mária Bohorodička',
            $day->month === 1 && $day->day === 6 => 'Zjavenie Pána',
            $day->eq($this->baptismOfTheLord($day->year)) => 'Krst Krista Pána',
            $day->month === 12 && $day->dayOfWeek === 0 => 'Svätá rodina Ježiša, Márie a Jozefa',
            $day->month === 12 => "{$weekday} v Oktáve Narodenia Pána",
            $day->dayOfWeek === 0 => '2. nedeľa po narodení Pána',
            default => "{$weekday} vo Vianočnom období",
        };
    }

    /**
     * „1 deň", „3 dni", „76 dní". Nie cez trans_choice — ten pri texte, ktorý
     * nie je v slovenských prekladoch, skloňuje podľa záložnej angličtiny.
     */
    private function daysLabel(int $days): string
    {
        return $days.' '.match (true) {
            $days === 1 => 'deň',
            $days >= 2 && $days <= 4 => 'dni',
            default => 'dní',
        };
    }

    private function liturgicalYearOf(CarbonImmutable $day): int
    {
        return $day->gte($this->adventStart($day->year)) ? $day->year + 1 : $day->year;
    }

    private function normalize(CarbonInterface|string $date): CarbonImmutable
    {
        $ymd = $date instanceof CarbonInterface ? $date->format('Y-m-d') : substr($date, 0, 10);

        return CarbonImmutable::createFromFormat('!Y-m-d', $ymd, 'UTC');
    }

    private function ymd(int $year, int $month, int $day): CarbonImmutable
    {
        return CarbonImmutable::create($year, $month, $day, 0, 0, 0, 'UTC');
    }

    /** Počet dní od `$from` do `$to` (obe o polnoci v UTC). */
    private function days(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return intdiv($to->getTimestamp() - $from->getTimestamp(), 86400);
    }
}
