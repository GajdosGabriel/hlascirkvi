<?php

namespace App\Services\Liturgy;

use App\Enums\LiturgicalRank;
use App\Enums\PostSection;
use App\Models\Canal;
use App\Models\LiturgicalDay;
use App\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Liturgické čítania pre web: modul v bočnom paneli aj stránka /citania.
 *
 * Deň sa berie z `liturgical_days`, ktorú plní príkaz liturgia:stiahnut.
 * Keď v nej chýba, skúsi sa stiahnuť hneď (s krátkym limitom), a keď ani
 * to nevyjde, stránka ukáže aspoň vypočítaný kalendár — názov dňa, cyklus
 * a odkaz na KBS. Modul teda nikdy nezmizne ani nespadne.
 */
class DailyReadings
{
    public const CACHE_PREFIX = 'liturgy:';

    /** Značka v cache pre „stiahnuť sa nepodarilo, chvíľu to neskúšaj". */
    protected const MISSING = 'missing';

    public function __construct(
        protected LiturgicalCalendar $calendar,
        protected KbsReadingsClient $client,
        protected KbsReadingsParser $parser,
    ) {}

    public function forDate(CarbonInterface|string $date): DayReadings
    {
        $day = $this->calendar->for($date);

        return new DayReadings($day, $this->record($day), $this->client->dayUrl($day->date));
    }

    /**
     * Stiahne deň z KBS a uloží ho. Null, keď KBS neodpovedá alebo stránke
     * nerozumieme (vtedy ostáva prípadný starší záznam nedotknutý).
     */
    public function refresh(CarbonInterface|string $date, ?int $timeout = null): ?LiturgicalDay
    {
        $day = $this->calendar->for($date);
        $html = $this->client->day($day->date, $timeout);

        if ($html === null) {
            return null;
        }

        $parsed = $this->parser->parseDay($html, (bool) config('liturgy.full_texts'));

        if ($parsed === null || $parsed['sections'] === []) {
            Log::warning('Liturgický kalendár KBS: stránke dňa nerozumieme', ['date' => $day->date->toDateString()]);

            return null;
        }

        $title = $parsed['title'] ?: $day->title;

        // Pri ľubovoľnej spomienke KBS píše „Štvrtok 24. týždňa … alebo Svätého
        // Róberta …". Názov férie dáva kalendár, v zázname ostanú len svätí.
        if ($parsed['rank'] === LiturgicalRank::OptionalMemorial) {
            $title = preg_replace('/^(Pondelok|Utorok|Streda|Štvrtok|Piatok|Sobota)\s.*?\salebo\s+/u', '', $title) ?: $title;
        }

        $record = LiturgicalDay::updateOrCreate(['date' => $day->date->format('Y-m-d')], [
            'title' => Str::limit($title, 188),
            'rank' => $parsed['rank'] ?? ($day->isSunday() ? LiturgicalRank::Sunday : LiturgicalRank::Feria),
            'color' => $parsed['color'] ?? $day->color,
            'season' => $day->season,
            'week' => $day->week,
            'sunday_cycle' => $day->sundayCycle,
            'weekday_cycle' => $day->weekdayCycle,
            'psalter_week' => $day->psalterWeek,
            'obligation' => $parsed['obligation'],
            // V zátvorke nebol stupeň slávenia, ale poznámka (pôst na Popolcovú stredu).
            'note' => $parsed['rank'] === null && $parsed['rank_text'] !== null
                ? Str::limit($parsed['rank_text'], 188)
                : null,
            'readings' => $parsed['sections'],
            'source_url' => $this->client->dayUrl($day->date),
            'fetched_at' => now(),
        ]);

        $this->forget($day->date);

        return $record;
    }

    public function forget(CarbonInterface|string $date): void
    {
        Cache::forget($this->key($this->calendar->for($date)));
    }

    /**
     * Videá z archívu k tomu istému nedeľnému evanjeliu: nedeľné čítania sa
     * opakujú po troch rokoch, takže sa hľadá v okolí tej istej nedele pred
     * 3, 6 a 9 rokmi (config liturgy.homily_*). Len pre nedele — všedné dni
     * majú evanjelium každý rok rovnaké a homílií k nim je málo.
     *
     * @return Collection<int, array{id: int, slug: ?string, title: string, canal: ?string, published_at: string, years_ago: int}>
     */
    public function homilies(LiturgicalDate $day): Collection
    {
        if (! $day->isSunday()) {
            return collect();
        }

        // Do cache ide holé pole — config/cache.php nepovoľuje objekty.
        $rows = Cache::remember(
            self::CACHE_PREFIX.'homilies:'.$day->date->format('Y-m-d'),
            now()->addHours(6),
            fn () => $this->findHomilies($day)
        );

        return collect($rows);
    }

    /** @return array<int, array<string, mixed>> */
    protected function findHomilies(LiturgicalDate $day): array
    {
        $limit = max(1, (int) config('liturgy.homily_limit', 3));
        $window = max(0, (int) config('liturgy.homily_window_days', 2));
        $keywords = (array) config('liturgy.homily_keywords', []);
        $found = [];

        foreach ((array) config('liturgy.homily_years_back', [3, 6, 9]) as $yearsBack) {
            $date = $this->calendar->sameDayInCycle($day, (int) $yearsBack);

            if ($date === null) {
                continue;
            }

            $posts = Post::published()
                ->without(['favorites', 'images', 'canal'])
                ->whereBetween('published_at', [
                    $date->subDays($window)->startOfDay(),
                    $date->addDays($window)->endOfDay(),
                ])
                ->where(function ($query) use ($keywords) {
                    $query->where('section', PostSection::Live);

                    foreach ($keywords as $keyword) {
                        $query->orWhere('title', 'like', '%'.$keyword.'%');
                    }
                })
                // Príspevok vypnutého kanála detail odmietne — nemá zmysel naň odkazovať.
                ->whereIn('canal_id', Canal::query()->where('published', 1)->select('id'))
                ->orderByDesc('count_view')
                ->limit($limit - count($found))
                ->get(['id', 'slug', 'title', 'canal_id', 'published_at']);

            $canals = Canal::query()->whereIn('id', $posts->pluck('canal_id'))->pluck('title', 'id');

            foreach ($posts as $post) {
                $found[] = [
                    'id' => $post->id,
                    'slug' => $post->slug,
                    'title' => (string) $post->title,
                    'canal' => $canals[$post->canal_id] ?? null,
                    'published_at' => $post->published_at->toDateString(),
                    'years_ago' => (int) $yearsBack,
                ];
            }

            if (count($found) >= $limit) {
                break;
            }
        }

        return $found;
    }

    protected function record(LiturgicalDate $day): ?LiturgicalDay
    {
        $key = $this->key($day);
        $cached = Cache::get($key);

        if (is_array($cached)) {
            return (new LiturgicalDay)->newFromBuilder($cached);
        }

        if ($cached === self::MISSING) {
            return null;
        }

        $record = LiturgicalDay::forDate($day->date)->first();

        if ($record === null && config('liturgy.lazy_fetch')) {
            $record = $this->refresh($day->date, (int) config('liturgy.lazy_timeout', 4));
        }

        if ($record === null) {
            Cache::put($key, self::MISSING, now()->addMinutes((int) config('liturgy.retry_minutes', 10)));

            return null;
        }

        Cache::put($key, $record->getAttributes(), now()->addHours(6));

        return $record;
    }

    protected function key(LiturgicalDate $day): string
    {
        return self::CACHE_PREFIX.'day:'.$day->date->format('Y-m-d');
    }
}
