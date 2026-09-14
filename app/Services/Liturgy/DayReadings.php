<?php

namespace App\Services\Liturgy;

use App\Enums\LiturgicalColor;
use App\Enums\LiturgicalRank;
use App\Enums\ReadingType;
use App\Models\LiturgicalDay;
use Illuminate\Support\Str;

/**
 * Čo o dni vieme pre zobrazenie: vypočítaný kalendár a — ak sa podaril —
 * záznam z KBS. Šablóny sa pýtajú len tejto triedy, nemusia riešiť, či
 * záznam existuje.
 */
final readonly class DayReadings
{
    public function __construct(
        public LiturgicalDate $calendar,
        public ?LiturgicalDay $record,
        public string $sourceUrl,
    ) {}

    public function hasReadings(): bool
    {
        return $this->mainSection() !== null;
    }

    /**
     * Pri ľubovoľnej spomienke ostáva dňom féria (a jej čítania), svätí
     * idú do podtitulku. Inak platí názov z KBS.
     */
    public function title(): string
    {
        if ($this->record === null || $this->record->rank === LiturgicalRank::OptionalMemorial) {
            return $this->calendar->title;
        }

        return $this->record->title;
    }

    public function subtitle(): ?string
    {
        $record = $this->record;

        return match ($record?->rank) {
            null => null,
            LiturgicalRank::OptionalMemorial => 'Ľubovoľná spomienka: '.$record->title,
            LiturgicalRank::Solemnity,
            LiturgicalRank::Feast,
            LiturgicalRank::Memorial => Str::ucfirst($record->rank->label()),
            default => $record->note,
        };
    }

    public function color(): LiturgicalColor
    {
        if ($this->record === null || $this->record->rank === LiturgicalRank::OptionalMemorial) {
            return $this->calendar->color;
        }

        return $this->record->color;
    }

    public function obligation(): bool
    {
        return (bool) $this->record?->obligation;
    }

    /** @return array<int, array<string, mixed>> */
    public function sections(): array
    {
        return array_map(fn (array $section) => $this->labelled($section), $this->record?->sections() ?? []);
    }

    /** @return array<string, mixed>|null */
    public function mainSection(): ?array
    {
        $section = $this->record?->mainSection();

        return $section ? $this->labelled($section) : null;
    }

    /**
     * Doplní riadkom popis „1. čítanie", „Responzóriový žalm", „Evanjelium".
     * Prvé a druhé čítanie sa v dátach nelíši typom, len poradím.
     *
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private function labelled(array $section): array
    {
        $reading = 0;

        foreach ($section['lines'] ?? [] as $i => $line) {
            $type = ReadingType::tryFrom($line['type'] ?? '') ?? ReadingType::Reading;

            $section['lines'][$i]['label'] = match ($type) {
                ReadingType::Reading => (++$reading).'. čítanie',
                ReadingType::Psalm => 'Responzóriový žalm',
                ReadingType::Sequence => 'Sekvencia',
                ReadingType::Gospel => 'Evanjelium',
            };

            // Kotva pre odkaz z modulu na čítanie na /citania.
            foreach ($line['options'] ?? [] as $j => $option) {
                $section['lines'][$i]['options'][$j]['anchor'] = 'citanie-'.Str::slug((string) ($option['citation'] ?? ''));
            }
        }

        return $section;
    }
}
