<?php

namespace App\Services\Buffer;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Rozvrh zverejnení na jeden deň.
 *
 * Plán je deterministický: rovnaký dátum a rovnako veľký front dajú vždy tie
 * isté časy. Vďaka tomu si ho nemusí nikde ukladať — cron ho pri každom behu
 * prepočíta nanovo a porovná s tým, čo už dnes vyšlo. Náhodnosť je iba zdanlivá
 * (seed je dátum), takže časy vyzerajú ľudsky nepravidelne, ale počas dňa sa
 * neprehadzujú.
 */
class PublishPlan
{
    protected array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? config('buffer');
    }

    /**
     * Časy zverejnení pre daný deň.
     *
     * @param  int  $backlog  koľko príspevkov bolo v bufferi na začiatku dňa
     * @param  float  $inflow  koľko ich denne pribúda (priemer za posledné dni)
     * @return list<CarbonImmutable>
     */
    public function forDate(CarbonInterface $date, int $backlog, float $inflow = 0.0): array
    {
        $day = CarbonImmutable::instance($date)->startOfDay();
        $windows = $this->windows($day);

        if ($windows === []) {
            return [];
        }

        // Seed z dátumu — plán sa počas dňa nemení, ale zajtra je iný.
        $random = new Randomizer(new Mt19937(crc32('buffer:'.$day->toDateString())));

        $count = min($this->dailyCount($backlog, $inflow, $random), $this->capacity($windows));

        if ($count === 0) {
            return [];
        }

        $slots = [];

        foreach ($this->pickWindows($windows, $count, $random) as $window) {
            $slots[] = $this->momentInside($day, $window, $random);
        }

        usort($slots, fn ($a, $b) => $a <=> $b);

        return $this->keepApart($slots, $this->endOfDay($day, $windows), $random);
    }

    /**
     * Okná podľa dňa v týždni — všedný deň, sobota, nedeľa.
     *
     * @return list<array{from: string, to: string, weight?: int}>
     */
    protected function windows(CarbonImmutable $day): array
    {
        if ($day->isSunday()) {
            return $this->config['windows_sunday'] ?? $this->config['windows_weekend'] ?? $this->config['windows'];
        }

        if ($day->isSaturday()) {
            return $this->config['windows_weekend'] ?? $this->config['windows'];
        }

        return $this->config['windows'];
    }

    /**
     * Koľko príspevkov dnes pustiť.
     *
     * Prvá časť pokryje denný prítok, aby front nerástol. Druhá rozpúšťa to,
     * čo v ňom leží navyše — archív starých videí tak ide von po kúskoch
     * a nezdrží čerstvé prírastky. Jednotka hore-dole je preto, aby počet
     * nebol každý deň na chlp rovnaký.
     */
    protected function dailyCount(int $backlog, float $inflow, Randomizer $random): int
    {
        if ($backlog < 1) {
            return 0;
        }

        $daily = $this->config['daily'];

        $count = (int) ceil($inflow);

        // Koľko smie vo fronte ležať bez toho, aby publisher pridal na tempe.
        $reserve = (int) ceil($inflow * (float) $daily['keep_ahead_days']);
        $excess = max(0, $backlog - $reserve);

        if ($excess > 0) {
            $count += (int) ceil($excess / max(1, (int) $daily['drain_days']));
        }

        $count = min((int) $daily['max'], $count);
        $count -= $random->getInt(0, 1);
        $count = max((int) $daily['min'], $count);

        return min($count, $backlog);
    }

    /**
     * Koľko zverejnení sa do dňa vôbec zmestí, aby medzi nimi zostal priestor
     * a nešli tesne na doraz minimálneho odstupu. Cez víkend, keď je okien
     * menej, tým deň sám od seba spomalí — tak, ako by to spravil človek.
     *
     * @param  list<array{from: string, to: string, weight?: int}>  $windows
     */
    protected function capacity(array $windows): int
    {
        $minutes = 0;

        foreach ($windows as $window) {
            $minutes += max(0, (strtotime($window['to']) - strtotime($window['from'])) / 60);
        }

        $gap = max(1, (int) $this->config['min_gap_minutes']);

        return (int) max(1, floor($minutes / ($gap * 1.6)));
    }

    /**
     * Rozdelí zverejnenia do okien podľa váh. Jedno okno neunesie viac ako
     * priemer na okno, takže sa celý deň nezmestí do jedného podvečera.
     *
     * @param  list<array{from: string, to: string, weight?: int}>  $windows
     * @return list<array{from: string, to: string, weight?: int}>
     */
    protected function pickWindows(array $windows, int $count, Randomizer $random): array
    {
        $limit = (int) max(1, ceil($count / count($windows)));
        $used = array_fill(0, count($windows), 0);
        $picked = [];

        while (count($picked) < $count) {
            $bag = [];

            foreach ($windows as $index => $window) {
                if ($used[$index] >= $limit) {
                    continue;
                }

                for ($n = 0; $n < max(1, (int) ($window['weight'] ?? 1)); $n++) {
                    $bag[] = $index;
                }
            }

            // Všetky okná sú plné — povolíme im o jedno viac a skúsime znova.
            if ($bag === []) {
                $limit++;

                continue;
            }

            $index = $bag[$random->getInt(0, count($bag) - 1)];
            $used[$index]++;
            $picked[] = $windows[$index];
        }

        return $picked;
    }

    /**
     * Náhodný okamih vnútri okna — vrátane sekúnd, aby časy nesedeli na
     * päťminútovom rastri cronu.
     *
     * @param  array{from: string, to: string, weight?: int}  $window
     */
    protected function momentInside(CarbonImmutable $day, array $window, Randomizer $random): CarbonImmutable
    {
        $from = $day->setTimeFromTimeString($window['from']);
        $to = $day->setTimeFromTimeString($window['to']);
        $length = max(0, $to->getTimestamp() - $from->getTimestamp());

        return $from->addSeconds($random->getInt(0, $length));
    }

    /**
     * Ustráži minimálny odstup. Sloty, ktoré si po posunutí prelezú za posledné
     * okno, radšej zahodí — deň nemá končiť dávkou o polnoci.
     *
     * @param  list<CarbonImmutable>  $slots
     * @return list<CarbonImmutable>
     */
    protected function keepApart(array $slots, CarbonImmutable $endOfDay, Randomizer $random): array
    {
        $gap = max(1, (int) $this->config['min_gap_minutes']);
        $kept = [];
        $previous = null;

        foreach ($slots as $slot) {
            if ($previous !== null && $slot->lt($previous->addMinutes($gap))) {
                $slot = $previous->addMinutes($gap + $random->getInt(0, 9));
            }

            if ($slot->gt($endOfDay)) {
                break;
            }

            $kept[] = $slot;
            $previous = $slot;
        }

        return $kept;
    }

    /**
     * @param  list<array{from: string, to: string, weight?: int}>  $windows
     */
    protected function endOfDay(CarbonImmutable $day, array $windows): CarbonImmutable
    {
        $last = $day->startOfDay();

        foreach ($windows as $window) {
            $end = $day->setTimeFromTimeString($window['to']);

            if ($end->gt($last)) {
                $last = $end;
            }
        }

        return $last;
    }
}
