<?php

namespace App\Services\Dashboard;

use App\Models\Canal;
use App\Models\LiturgicalDay;
use App\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Súhrnné čísla za celý web pre úvod administrácie.
 *
 * Rovnako ako DashboardStats ide všetko cez query builder — Post aj Canal
 * majú $with a $appends a modely by tu len násobili dopyty. Okno porovnania
 * (30 dní proti 30 predtým) je to isté ako na nástenke kanála, aby čísla
 * oboch miest šli vedľa seba.
 */
class AdminDashboardStats
{
    public const WINDOW = DashboardStats::WINDOW;

    /** Koľko dní dozadu sa skladá mapa „kedy sa číta“ — štyri celé týždne. */
    public const RHYTHM_DAYS = 28;

    public const CACHE_KEY = 'admin:dashboard';

    /**
     * Ako dlho platí zacachovaná nástenka. Súhrny cez posts, comments
     * a prayers idú cez celé tabuľky a trvajú dohromady vyše sekundy;
     * scheduler ich obnovuje každých päť minút (warm()), takže administrátor
     * na výpočet čaká len výnimočne.
     */
    public const CACHE_TTL = 600;

    /** Čísla z cache; $fresh ich prepočíta hneď (odkaz „Obnoviť“). */
    public function cached(bool $fresh = false): array
    {
        $json = $fresh ? null : Cache::get(self::CACHE_KEY);
        $stats = is_string($json) ? $this->hydrate($json) : $this->warm();

        // Liturgický deň je jeden rýchly dopyt a musí sedieť s dneškom aj
        // tesne po polnoci, preto ide mimo cache.
        $stats['liturgy'] = LiturgicalDay::query()->forDate(CarbonImmutable::now())->first();

        return $stats;
    }

    /**
     * Prepočíta nástenku a uloží ju do cache. Ukladá sa ako JSON —
     * config/cache.php zámerne nedovolí z cache rozbaliť PHP objekty.
     */
    public function warm(): array
    {
        $stats = $this->get();
        unset($stats['liturgy']);

        Cache::put(self::CACHE_KEY, json_encode($stats), self::CACHE_TTL);

        return $stats;
    }

    /** Späť z JSON do tvaru, aký vracia get() a čaká šablóna. */
    protected function hydrate(string $json): array
    {
        $stats = (array) json_decode($json);
        $date = fn (string $value) => CarbonImmutable::parse($value)->setTimezone(config('app.timezone'));

        $stats['now'] = $date($stats['now']);
        $stats['timeline'] = collect($stats['timeline'])->each(fn ($row) => $row->day = $date($row->day));
        $stats['activity'] = collect($stats['activity'])->each(fn ($row) => $row->month = $date($row->month));
        $stats['denominations'] = (array) $stats['denominations'];

        foreach (['topPosts', 'risingCanals', 'latestComments', 'newestUsers', 'newestCanals'] as $key) {
            $stats[$key] = collect($stats[$key]);
        }

        return $stats;
    }

    public function get(?CarbonImmutable $now = null): array
    {
        $now = $now ?: CarbonImmutable::now();
        $timeline = $this->timeline($now);

        return [
            'now' => $now,
            'users' => $this->users($now),
            'canals' => $this->canals($now),
            'posts' => $this->posts($now),
            'comments' => $this->comments($now),
            'prayers' => $this->prayers($now),
            'timeline' => $timeline,
            'views' => $this->viewsSummary($timeline),
            'activity' => $this->activity($now),
            'rhythm' => $this->rhythm($now),
            'topPosts' => $this->topPosts($now),
            'risingCanals' => $this->risingCanals($now),
            'latestComments' => $this->latestComments(),
            'newestUsers' => $this->newestUsers(),
            'newestCanals' => $this->newestCanals(),
            'favorites' => $this->favorites(),
            'denominations' => $this->denominations(),
            'ai' => $this->ai($now),
            'buffer' => $this->buffer($now),
        ];
    }

    /** Percentuálna zmena; z nulového základu sa nepočíta, nič by nepovedala. */
    public static function change(int|float $current, int|float $previous): ?int
    {
        return $previous > 0 ? (int) round(($current - $previous) / $previous * 100) : null;
    }

    protected function users(CarbonImmutable $now): object
    {
        $from = $now->subDays(self::WINDOW);
        $prev = $now->subDays(self::WINDOW * 2);

        return DB::table('users')
            ->whereNull('deleted_at')
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(created_at >= ?), 0) as new', [$from])
            ->selectRaw('coalesce(sum(created_at >= ? and created_at < ?), 0) as new_previous', [$prev, $from])
            ->selectRaw('coalesce(sum(last_login_at >= ?), 0) as active', [$from])
            ->selectRaw("coalesce(sum(status = 'blocked'), 0) as blocked")
            ->selectRaw('coalesce(sum(email_verified_at is null), 0) as unverified')
            ->first();
    }

    protected function canals(CarbonImmutable $now): object
    {
        $row = DB::table('canals')
            ->whereNull('deleted_at')
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(published = 1), 0) as published')
            ->selectRaw('coalesce(sum(created_at >= ?), 0) as new', [$now->subDays(self::WINDOW)])
            ->selectRaw('coalesce(sum(youtube_disabled_at is not null), 0) as youtube_off')
            ->first();

        // Rovnaká definícia ako dlaždica „Bez správcu“ vo výpise kanálov.
        $row->orphans = DB::table('canals')
            ->whereNull('deleted_at')
            ->whereNotExists(fn ($q) => $q->from('canal_user')->whereColumn('canal_user.canal_id', 'canals.id'))
            ->count();

        return $row;
    }

    protected function posts(CarbonImmutable $now): object
    {
        $from = $now->subDays(self::WINDOW);
        $prev = $now->subDays(self::WINDOW * 2);

        return DB::table('posts')
            ->where('youtube_blocked', 0)
            ->selectRaw('coalesce(sum(deleted_at is null), 0) as total')
            ->selectRaw('coalesce(sum(deleted_at is null and published_at is not null), 0) as published')
            ->selectRaw('coalesce(sum(deleted_at is null and published_at is null), 0) as waiting')
            ->selectRaw('coalesce(sum(deleted_at is null and published_at >= ?), 0) as published_today', [$now->startOfDay()])
            ->selectRaw('coalesce(sum(deleted_at is null and video_available = 0), 0) as broken')
            ->selectRaw('coalesce(sum(case when deleted_at is null then count_view end), 0) as views')
            ->selectRaw('coalesce(sum(case when deleted_at is null then video_duration end), 0) as duration')
            ->selectRaw('coalesce(sum(deleted_at is null and summary is not null), 0) as summarized')
            ->selectRaw('coalesce(sum(deleted_at is null and created_at >= ?), 0) as new', [$from])
            ->selectRaw('coalesce(sum(deleted_at is null and created_at >= ? and created_at < ?), 0) as new_previous', [$prev, $from])
            ->first();
    }

    protected function comments(CarbonImmutable $now): object
    {
        $from = $now->subDays(self::WINDOW);
        $prev = $now->subDays(self::WINDOW * 2);

        return DB::table('comments')
            ->whereNull('deleted_at')
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(created_at >= ?), 0) as new', [$from])
            ->selectRaw('coalesce(sum(created_at >= ? and created_at < ?), 0) as new_previous', [$prev, $from])
            ->selectRaw('coalesce(sum(published = 0), 0) as unpublished')
            ->selectRaw('coalesce(sum(youtube_comment_id is not null), 0) as youtube')
            ->selectRaw('coalesce(sum(parent_id is not null), 0) as replies')
            ->first();
    }

    protected function prayers(CarbonImmutable $now): object
    {
        return DB::table('prayers')
            ->whereNull('deleted_at')
            ->selectRaw('coalesce(sum(fulfilled_at is null), 0) as open')
            ->selectRaw('coalesce(sum(fulfilled_at is not null), 0) as fulfilled')
            ->selectRaw('coalesce(sum(created_at >= ?), 0) as new', [$now->subDays(self::WINDOW)])
            ->first();
    }

    /**
     * Zhliadnutia celého webu po dňoch za dve okná. Tvar riadkov je rovnaký
     * ako v DashboardStats::timeline(), takže graf dashboard._chart ich
     * nakreslí bez úprav.
     *
     * @return Collection<int, object>
     */
    protected function timeline(CarbonImmutable $now): Collection
    {
        $start = $now->subDays(self::WINDOW * 2 - 1)->startOfDay();

        $rows = DB::table('views')
            ->where('viewable_type', Post::class)
            ->where('viewed_on', '>=', $start->toDateString())
            ->groupBy('viewed_on')
            ->pluck(DB::raw('count(*)'), 'viewed_on');

        return collect(range(0, self::WINDOW * 2 - 1))->map(function ($offset) use ($start, $rows) {
            $day = $start->addDays($offset);

            return (object) [
                'day' => $day,
                'views' => (int) ($rows[$day->toDateString()] ?? 0),
            ];
        });
    }

    protected function viewsSummary(Collection $timeline): object
    {
        $window = $timeline->slice(self::WINDOW);
        $current = (int) $window->sum('views');
        $previous = (int) $timeline->take(self::WINDOW)->sum('views');

        return (object) [
            'today' => (int) $timeline->last()->views,
            'yesterday' => (int) $timeline->slice(-2, 1)->first()->views,
            'current' => $current,
            'previous' => $previous,
            'change' => self::change($current, $previous),
            'peak' => (int) $window->max('views'),
            'average' => (int) round($current / self::WINDOW),
        ];
    }

    /** @return Collection<int, object> nové príspevky po mesiacoch za rok */
    protected function activity(CarbonImmutable $now): Collection
    {
        $start = $now->startOfMonth()->subMonths(11);

        $rows = DB::table('posts')
            ->where('youtube_blocked', 0)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $start)
            ->groupBy('bucket')
            ->pluck(DB::raw('count(*)'), DB::raw("date_format(created_at, '%Y-%m') as bucket"));

        return collect(range(0, 11))->map(function ($offset) use ($start, $rows) {
            $month = $start->addMonths($offset);

            return (object) [
                'month' => $month,
                'posts' => (int) ($rows[$month->format('Y-m')] ?? 0),
            ];
        });
    }

    /**
     * Kedy ľudia čítajú: zhliadnutia po dňoch v týždni a hodinách. Riadok
     * v `views` vzniká pri prvom otvorení príspevku v daný deň, takže
     * `created_at` je chvíľa, keď čitateľ prišiel. Vedľa toho hodiny, keď
     * príspevky vychádzajú — či obsah chodí vtedy, keď naň ľudia čakajú.
     *
     * @return object{grid: array<int, array<int, int>>, max: int, published: array<int, int>, bestDay: ?int, bestHour: ?int}
     */
    protected function rhythm(CarbonImmutable $now): object
    {
        $from = $now->subDays(self::RHYTHM_DAYS)->startOfDay();

        $grid = array_fill(0, 7, array_fill(0, 24, 0));

        DB::table('views')
            ->where('viewable_type', Post::class)
            ->where('viewed_on', '>=', $from->toDateString())
            ->groupBy('d', 'h')
            ->get([DB::raw('weekday(created_at) as d'), DB::raw('hour(created_at) as h'), DB::raw('count(*) as c')])
            ->each(function ($row) use (&$grid) {
                $grid[(int) $row->d][(int) $row->h] = (int) $row->c;
            });

        $published = array_fill(0, 24, 0);

        DB::table('posts')
            ->where('youtube_blocked', 0)
            ->whereNull('deleted_at')
            ->where('published_at', '>=', $from)
            ->groupBy('h')
            ->get([DB::raw('hour(published_at) as h'), DB::raw('count(*) as c')])
            ->each(function ($row) use (&$published) {
                $published[(int) $row->h] = (int) $row->c;
            });

        $days = array_map('array_sum', $grid);
        $hours = array_map(fn ($h) => array_sum(array_column($grid, $h)), range(0, 23));
        $max = max(array_map('max', $grid));

        return (object) [
            'grid' => $grid,
            'max' => $max,
            'hours' => $hours,
            'published' => $published,
            'bestDay' => $max > 0 ? array_search(max($days), $days, true) : null,
            'bestHour' => $max > 0 ? array_search(max($hours), $hours, true) : null,
        ];
    }

    /** @return Collection<int, object> najčítanejšie za okno naprieč webom */
    protected function topPosts(CarbonImmutable $now, int $limit = 6): Collection
    {
        return DB::table('views')
            ->join('posts', 'posts.id', '=', 'views.viewable_id')
            ->leftJoin('canals', 'canals.id', '=', 'posts.canal_id')
            ->where('views.viewable_type', Post::class)
            ->whereNull('posts.deleted_at')
            ->where('views.viewed_on', '>=', $now->subDays(self::WINDOW)->toDateString())
            ->groupBy('posts.id', 'posts.title', 'posts.slug', 'posts.count_view', 'canals.title')
            ->orderByDesc('period_views')
            ->limit($limit)
            ->get([
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.count_view',
                'canals.title as canal',
                DB::raw('count(*) as period_views'),
            ]);
    }

    /**
     * Kanály s najväčšou čítanosťou za okno a ako sa im darilo v okne pred
     * ním. Rastúci kanál s malým základom je často nový — taký, ktorému sa
     * oplatí venovať pozornosť (napr. zaradiť ho do predného zoznamu).
     *
     * @return Collection<int, object>
     */
    protected function risingCanals(CarbonImmutable $now, int $limit = 6): Collection
    {
        $from = $now->subDays(self::WINDOW)->toDateString();
        $prev = $now->subDays(self::WINDOW * 2)->toDateString();

        return DB::table('views')
            ->join('posts', 'posts.id', '=', 'views.viewable_id')
            ->join('canals', 'canals.id', '=', 'posts.canal_id')
            ->where('views.viewable_type', Post::class)
            ->where('views.viewed_on', '>=', $prev)
            ->whereNull('canals.deleted_at')
            ->groupBy('canals.id', 'canals.title')
            ->select('canals.id', 'canals.title')
            ->selectRaw('coalesce(sum(views.viewed_on >= ?), 0) as current', [$from])
            ->selectRaw('coalesce(sum(views.viewed_on < ?), 0) as previous', [$from])
            ->havingRaw('current > 0')
            ->orderByDesc('current')
            ->limit($limit)
            ->get()
            ->each(function ($row) {
                $row->current = (int) $row->current;
                $row->previous = (int) $row->previous;
                $row->change = self::change($row->current, $row->previous);
            });
    }

    /** @return Collection<int, object> posledné komentáre aj s článkom, pod ktorým sú */
    protected function latestComments(int $limit = 6): Collection
    {
        return DB::table('comments')
            ->leftJoin('posts', function ($join) {
                $join->on('posts.id', '=', 'comments.commentable_id')
                    ->where('comments.commentable_type', Post::class);
            })
            ->whereNull('comments.deleted_at')
            ->orderByDesc('comments.created_at')
            ->limit($limit)
            ->get([
                'comments.id',
                'comments.body',
                'comments.user_name',
                'comments.created_at',
                'comments.youtube_comment_id',
                'posts.id as post_id',
                'posts.title as post_title',
                'posts.slug as post_slug',
            ]);
    }

    /** @return Collection<int, object> */
    protected function newestUsers(int $limit = 5): Collection
    {
        return DB::table('users')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'email', 'avatar', 'created_at', 'last_login_via', 'email_verified_at']);
    }

    /** @return Collection<int, object> */
    protected function newestCanals(int $limit = 5): Collection
    {
        return DB::table('canals')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'title', 'published', 'created_at', 'youtube_channel']);
    }

    /** Čo si ľudia ukladajú do obľúbených. */
    protected function favorites(): object
    {
        $rows = DB::table('favorites')
            ->groupBy('favorited_type')
            ->pluck(DB::raw('count(*)'), 'favorited_type');

        return (object) [
            'canals' => (int) ($rows[(new Canal)->getMorphClass()] ?? 0),
            'posts' => (int) ($rows[Post::class] ?? 0),
            'prayers' => (int) ($rows[\App\Models\Prayer::class] ?? 0),
            'comments' => (int) ($rows[\App\Models\Comment::class] ?? 0),
            'saved' => (int) DB::table('saved_posts')->count(),
        ];
    }

    /** @return array<string, int> počet kanálov podľa cirkevného zaradenia; '' = nezaradené */
    protected function denominations(): array
    {
        return DB::table('canals')
            ->whereNull('deleted_at')
            ->groupBy('denomination')
            ->pluck(DB::raw('count(*)'), DB::raw("coalesce(denomination, '') as denomination"))
            ->map(fn ($count) => (int) $count)
            ->sortDesc()
            ->all();
    }

    protected function ai(CarbonImmutable $now): object
    {
        return DB::table('ai_usages')
            ->where('created_at', '>=', $now->startOfMonth())
            ->selectRaw('count(*) as calls')
            ->selectRaw('coalesce(sum(cost_usd), 0) as cost')
            ->selectRaw('coalesce(sum(prompt_tokens + completion_tokens), 0) as tokens')
            ->first();
    }

    protected function buffer(CarbonImmutable $now): object
    {
        return DB::table('buffer_publications')
            ->where('slot_at', '>=', $now)
            ->selectRaw('count(*) as queued')
            ->selectRaw('min(slot_at) as next_at')
            ->selectRaw('max(slot_at) as last_at')
            ->first();
    }
}
