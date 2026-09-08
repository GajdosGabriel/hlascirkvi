<?php

namespace App\Services\Dashboard;

use App\Models\Organization;
use App\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Čísla nástenky kanála (/dashboard).
 *
 * Všetko ide zámerne cez query builder, nie cez modely: Post aj Organization
 * majú $with a $appends, takže načítanie modelov len kvôli súčtu by spustilo
 * niekoľko dopytov na každý riadok — viď App\Traits\HasFavorites.
 *
 * Na rozdiel od verejného profilu kanála tu vlastník vidí aj to, čo návštevník
 * nie: nezverejnené príspevky, zmazané a videá, ktoré na YouTube už nie sú.
 */
class DashboardStats
{
    /** Dĺžka porovnávaného okna v dňoch. */
    public const WINDOW = 30;

    public function for(Organization $organization, ?CarbonImmutable $now = null): array
    {
        $now = $now ?: CarbonImmutable::now();
        $id  = $organization->id;

        $posts    = $this->posts($id, $now);
        $timeline = $this->timeline($id, $now);

        return [
            'now'            => $now,
            'posts'          => $posts,
            'timeline'       => $timeline,
            'views'          => $this->viewsSummary($timeline),
            'activity'       => $this->activity($id, $now),
            'comments'       => $this->commentsSummary($id, $now),
            'prayers'        => $this->prayers($id, $now),
            'audience'       => $this->audience($id, $now),
            'seminars'       => $this->seminars($id),
            'topPosts'       => $this->topPosts($id, $now),
            'latestPosts'    => $this->latestPosts($id),
            'latestComments' => $this->latestComments($id),

            // Oba výpisy sú výber z tej istej sady príspevkov, ktorú už
            // spočítal súhrn vyššie, a `video_available` ani `published`
            // vlastný index nemajú. Keď je počet nulový, dopyt by len
            // pretriedil celý archív kanála pre prázdny zoznam.
            'waitingPosts'   => $posts->waiting > 0 ? $this->waitingPosts($id) : collect(),
            'brokenPosts'    => $posts->broken > 0 ? $this->brokenPosts($id) : collect(),
        ];
    }

    /**
     * Čísla pri záložkách v hlavičke správcovských stránok
     * (App\View\Components\Dashboard\Shell). Nástenka ich má už v súhrne,
     * ostatné sekcie potrebujú len tieto tri — a musia sedieť s tým, čo
     * ukazuje nástenka, preto stoja tu vedľa dopytov, z ktorých vychádzajú.
     */
    public function tabCounts(Organization $organization): array
    {
        $id = $organization->id;

        return [
            'posts' => (int) DB::table('posts')
                ->where('organization_id', $id)
                ->where('youtube_blocked', 0)
                ->whereNull('deleted_at')
                ->count(),
            'seminars' => (int) $this->seminars($id)->total,
            'prayers'  => (int) $this->prayers($id, CarbonImmutable::now())->total,
        ];
    }

    /**
     * Súhrn príspevkov jedným prechodom. Podmienené súčty sú tu preto, že
     * inak by to bolo šesť samostatných count() dopytov nad tou istou sadou
     * riadkov; takto stačí jeden prechod cez posts_organization_count_index.
     */
    protected function posts(int $organizationId, CarbonImmutable $now): object
    {
        $from = $now->subDays(self::WINDOW);
        $prev = $now->subDays(self::WINDOW * 2);

        return DB::table('posts')
            ->where('organization_id', $organizationId)
            ->where('youtube_blocked', 0)
            ->selectRaw('coalesce(sum(deleted_at is null), 0) as total')
            ->selectRaw('coalesce(sum(deleted_at is null and published is not null), 0) as published')
            ->selectRaw('coalesce(sum(deleted_at is null and published is null), 0) as waiting')
            ->selectRaw('coalesce(sum(deleted_at is not null), 0) as trashed')
            ->selectRaw('coalesce(sum(deleted_at is null and video_available = 0), 0) as broken')
            ->selectRaw('coalesce(sum(case when deleted_at is null then count_view end), 0) as views_total')
            ->selectRaw('min(case when deleted_at is null then created_at end) as first_at')
            ->selectRaw('max(case when deleted_at is null then created_at end) as last_at')
            ->selectRaw('coalesce(sum(deleted_at is null and created_at >= ?), 0) as new_window', [$from])
            ->selectRaw('coalesce(sum(deleted_at is null and created_at >= ? and created_at < ?), 0) as new_previous', [$prev, $from])
            ->first();
    }

    /**
     * Zhliadnutia po dňoch za dve okná dozadu — z nich sa kreslí graf aj
     * porovnanie s predchádzajúcim mesiacom.
     *
     * Tabuľka `views` nemá stĺpec s kanálom (zobrazenie visí polymorfne na
     * príspevku), preto join cez `posts`. Dopyt sedí na views_target_day_index
     * a vráti najviac 60 riadkov; prázdne dni dopĺňa až PHP, aby graf nemal
     * diery.
     *
     * @return Collection<int, object>
     */
    protected function timeline(int $organizationId, CarbonImmutable $now): Collection
    {
        $start = $now->subDays(self::WINDOW * 2 - 1)->startOfDay();

        $rows = DB::table('views')
            ->join('posts', 'posts.id', '=', 'views.viewable_id')
            ->where('views.viewable_type', Post::class)
            ->where('posts.organization_id', $organizationId)
            ->where('views.viewed_on', '>=', $start->toDateString())
            ->groupBy('views.viewed_on')
            ->pluck(DB::raw('count(*)'), 'views.viewed_on');

        return collect(range(0, self::WINDOW * 2 - 1))
            ->map(function ($offset) use ($start, $rows) {
                $day = $start->addDays($offset);

                return (object) [
                    'day'   => $day,
                    'views' => (int) ($rows[$day->toDateString()] ?? 0),
                ];
            });
    }

    /**
     * Zhliadnutia za posledné okno proti tomu pred ním. Zmena sa počíta len
     * vtedy, keď je z čoho — pri nulovom základe by percento nič nehovorilo.
     */
    protected function viewsSummary(Collection $timeline): object
    {
        $window   = $timeline->slice(self::WINDOW);
        $current  = (int) $window->sum('views');
        $previous = (int) $timeline->take(self::WINDOW)->sum('views');

        return (object) [
            'current'  => $current,
            'previous' => $previous,
            'change'   => $previous > 0 ? (int) round(($current - $previous) / $previous * 100) : null,
            'peak'     => (int) $window->max('views'),
        ];
    }

    /**
     * Koľko toho kanál vydal po mesiacoch za posledný rok. Kreslí sa z toho
     * pásik pod grafom zhliadnutí — na jeden pohľad je vidieť výpadky.
     *
     * @return Collection<int, object>
     */
    protected function activity(int $organizationId, CarbonImmutable $now): Collection
    {
        $start = $now->startOfMonth()->subMonths(11);

        $rows = DB::table('posts')
            ->where('organization_id', $organizationId)
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
     * Komentáre pod príspevkami kanála — celkom aj v oboch oknách.
     */
    protected function commentsSummary(int $organizationId, CarbonImmutable $now): object
    {
        $from = $now->subDays(self::WINDOW);
        $prev = $now->subDays(self::WINDOW * 2);

        $row = $this->commentsQuery($organizationId)
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(comments.created_at >= ?), 0) as current', [$from])
            ->selectRaw('coalesce(sum(comments.created_at >= ? and comments.created_at < ?), 0) as previous', [$prev, $from])
            ->first();

        // Rovnaké pravidlo ako pri zhliadnutiach: z nulového základu sa
        // percento nepočíta.
        $row->change = $row->previous > 0
            ? (int) round(($row->current - $row->previous) / $row->previous * 100)
            : null;

        return $row;
    }

    protected function prayers(int $organizationId, CarbonImmutable $now): object
    {
        return DB::table('prayers')
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at')
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(fulfilled_at is null), 0) as open')
            ->selectRaw('coalesce(sum(fulfilled_at is not null), 0) as fulfilled')
            ->selectRaw('coalesce(sum(created_at >= ?), 0) as current', [$now->subDays(self::WINDOW)])
            ->first();
    }

    /**
     * Odberatelia kanála. Obľúbené visia polymorfne, dopyt sedí na
     * favorites_favorited_index.
     */
    protected function audience(int $organizationId, CarbonImmutable $now): object
    {
        return DB::table('favorites')
            ->where('favorited_type', Organization::class)
            ->where('favorited_id', $organizationId)
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(created_at >= ?), 0) as current', [$now->subDays(self::WINDOW)])
            ->first();
    }

    protected function seminars(int $organizationId): object
    {
        return DB::table('seminars')
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at')
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(published is not null), 0) as published')
            ->first();
    }

    /**
     * Čo kanál ťahá práve teraz: príspevky zoradené podľa zhliadnutí za
     * posledné okno, nie podľa celkového súčtu. Inak by v paneli navždy
     * trónilo to isté video spred rokov.
     *
     * @return Collection<int, object>
     */
    protected function topPosts(int $organizationId, CarbonImmutable $now, int $limit = 5): Collection
    {
        return DB::table('views')
            ->join('posts', 'posts.id', '=', 'views.viewable_id')
            ->where('views.viewable_type', Post::class)
            ->where('posts.organization_id', $organizationId)
            ->whereNull('posts.deleted_at')
            ->where('views.viewed_on', '>=', $now->subDays(self::WINDOW)->toDateString())
            ->groupBy('posts.id', 'posts.title', 'posts.slug', 'posts.count_view')
            ->orderByDesc('period_views')
            ->limit($limit)
            ->get([
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.count_view',
                DB::raw('count(*) as period_views'),
            ]);
    }

    /**
     * Posledné príspevky aj s celkovým počtom zhliadnutí — kontrola, že
     * import beží a že sa nové veci dostávajú von.
     *
     * @return Collection<int, object>
     */
    protected function latestPosts(int $organizationId, int $limit = 6): Collection
    {
        return DB::table('posts')
            ->where('organization_id', $organizationId)
            ->where('youtube_blocked', 0)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'count_view', 'published', 'created_at', 'video_available']);
    }

    /**
     * Príspevky, ktoré ešte čakajú v bufferi na zverejnenie (published je
     * prázdne). Najstaršie hore — tie idú von ako prvé.
     *
     * @return Collection<int, object>
     */
    protected function waitingPosts(int $organizationId, int $limit = 5): Collection
    {
        return DB::table('posts')
            ->where('organization_id', $organizationId)
            ->where('youtube_blocked', 0)
            ->whereNull('deleted_at')
            ->whereNull('published')
            ->orderBy('created_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'created_at']);
    }

    /**
     * Videá, ktoré na YouTube už nie sú — jediná vec na nástenke, ktorá si
     * priamo pýta zásah vlastníka.
     *
     * @return Collection<int, object>
     */
    protected function brokenPosts(int $organizationId, int $limit = 5): Collection
    {
        return DB::table('posts')
            ->where('organization_id', $organizationId)
            ->where('youtube_blocked', 0)
            ->whereNull('deleted_at')
            ->where('video_available', 0)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'created_at']);
    }

    /**
     * Posledné komentáre pod príspevkami kanála. Holé riadky zámerne: cez
     * model by si každý komentár dotiahol celý príspevok aj s obrázkami.
     *
     * @return Collection<int, object>
     */
    protected function latestComments(int $organizationId, int $limit = 6): Collection
    {
        return $this->commentsQuery($organizationId)
            ->orderByDesc('comments.created_at')
            ->limit($limit)
            ->get([
                'comments.id',
                'comments.body',
                'comments.created_at',
                'comments.user_name',
                'posts.id as post_id',
                'posts.title as post_title',
                'posts.slug as post_slug',
            ]);
    }

    protected function commentsQuery(int $organizationId)
    {
        return DB::table('comments')
            ->join('posts', 'posts.id', '=', 'comments.commentable_id')
            ->where('comments.commentable_type', Post::class)
            ->where('posts.organization_id', $organizationId)
            ->where('posts.youtube_blocked', 0)
            ->whereNull('comments.deleted_at')
            ->whereNull('posts.deleted_at');
    }
}
