<?php

namespace App\Models;

use App\Enums\LiturgicalColor;
use App\Enums\LiturgicalRank;
use App\Enums\LiturgicalSeason;
use App\Enums\ReadingType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Čítania na jeden deň, stiahnuté z liturgického kalendára KBS.
 *
 * `readings` drží omše dňa (Narodenie Pána má tri, bežný deň jednu):
 *
 *     [{
 *         "title": "Počas dňa" | null,
 *         "lines": [{
 *             "type": "reading" | "psalm" | "sequence" | "gospel",
 *             "response": "Pane, ty buď našou spásou." | null,      // žalm
 *             "acclamation": "Klaniame sa ti, Kriste…" | null,     // evanjelium
 *             "options": [{                                         // alternatívy
 *                 "label": null | "alebo" | "alebo kratšie",
 *                 "citation": "Nm 21, 4c-9",
 *                 "intro": "Čítanie z Knihy Numeri",
 *                 "heading": "Ak sa pohryzený pozrie naň, ostane nažive",
 *                 "bible_url": "http://dkc.kbs.sk/?in=Nm21,4",
 *                 "text": null                                      // len pri config liturgy.full_texts
 *             }]
 *         }]
 *     }]
 */
class LiturgicalDay extends Model
{
    protected $fillable = [
        'date',
        'title',
        'rank',
        'color',
        'season',
        'week',
        'sunday_cycle',
        'weekday_cycle',
        'psalter_week',
        'obligation',
        'note',
        'readings',
        'source_url',
        'fetched_at',
    ];

    protected $casts = [
        'date' => 'date',
        'rank' => LiturgicalRank::class,
        'color' => LiturgicalColor::class,
        'season' => LiturgicalSeason::class,
        'week' => 'integer',
        'weekday_cycle' => 'integer',
        'psalter_week' => 'integer',
        'obligation' => 'boolean',
        'readings' => 'array',
        'fetched_at' => 'datetime',
    ];

    public function scopeForDate(Builder $query, CarbonInterface|string $date): Builder
    {
        $ymd = $date instanceof CarbonInterface ? $date->format('Y-m-d') : $date;

        return $query->where('date', $ymd);
    }

    /** @return array<int, array<string, mixed>> */
    public function sections(): array
    {
        return is_array($this->readings) ? $this->readings : [];
    }

    /**
     * Omša, ktorú ukáže modul. Pri viacerých omšiach (Vianoce, Veľká noc)
     * tá „cez deň" — na tú ide najviac ľudí.
     *
     * @return array<string, mixed>|null
     */
    public function mainSection(): ?array
    {
        $sections = $this->sections();

        foreach ($sections as $section) {
            if (preg_match('/cez deň|počas dňa/iu', (string) ($section['title'] ?? ''))) {
                return $section;
            }
        }

        return $sections[0] ?? null;
    }

    /** Prvá možnosť evanjelia hlavnej omše. @return array<string, mixed>|null */
    public function gospel(): ?array
    {
        foreach ($this->mainSection()['lines'] ?? [] as $line) {
            if (($line['type'] ?? null) === ReadingType::Gospel->value) {
                return $line['options'][0] ?? null;
            }
        }

        return null;
    }
}
