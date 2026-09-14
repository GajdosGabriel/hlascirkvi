<?php

namespace App\Services\Liturgy;

use App\Enums\LiturgicalColor;
use App\Enums\LiturgicalSeason;
use Carbon\CarbonImmutable;

/**
 * Jeden deň v liturgickom roku tak, ako ho vypočíta LiturgicalCalendar —
 * bez sviatkov svätých. Tie (a konkrétne perikopy) prináša až záznam
 * App\Models\LiturgicalDay stiahnutý z KBS.
 */
final readonly class LiturgicalDate
{
    public function __construct(
        public CarbonImmutable $date,
        /** Rok, v ktorom liturgický rok končí — od 1. adventnej nedele je to kalendárny rok + 1. */
        public int $liturgicalYear,
        public LiturgicalSeason $season,
        /**
         * Týždeň obdobia. V Pôste 0 = dni po Popolcovej strede, vo Veľkonočnom
         * trojdní poradie dňa (1 = Veľký piatok), vo Vianočnom období 0.
         */
        public int $week,
        /** Nedeľný cyklus A, B alebo C. */
        public string $sundayCycle,
        /** Feriálny cyklus 1 (nepárny rok) alebo 2 (párny rok). */
        public int $weekdayCycle,
        public ?int $psalterWeek,
        public LiturgicalColor $color,
        public string $title,
    ) {}

    public function isSunday(): bool
    {
        return $this->date->dayOfWeek === 0;
    }

    public function weekdayCycleRoman(): string
    {
        return $this->weekdayCycle === 1 ? 'I' : 'II';
    }

    /** V tvare, ako ho píše KBS: „A/II". */
    public function cycleLabel(): string
    {
        return $this->sundayCycle.'/'.$this->weekdayCycleRoman();
    }

    /**
     * Kúsky riadku s kontextom dňa: „Rok A", „cyklus II",
     * „24. týždeň v Cezročnom období", „4. týždeň žaltára".
     *
     * @return array<int, string>
     */
    public function contextParts(): array
    {
        $numbered = in_array($this->season, [
            LiturgicalSeason::Advent,
            LiturgicalSeason::Ordinary,
            LiturgicalSeason::Lent,
            LiturgicalSeason::Easter,
        ], true) && $this->week > 0;

        return array_values(array_filter([
            'Rok '.$this->sundayCycle,
            'cyklus '.$this->weekdayCycleRoman(),
            $numbered ? $this->week.'. týždeň '.$this->season->inLabel() : $this->season->label(),
            $this->psalterWeek ? $this->psalterWeek.'. týždeň žaltára' : null,
        ]));
    }
}
