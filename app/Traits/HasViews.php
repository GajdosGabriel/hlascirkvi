<?php

namespace App\Traits;

use App\Models\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Počítadlo zobrazení. Nahradilo balík cyrildewit/eloquent-viewable.
 *
 * Dve čísla, dva zdroje a to zámerne:
 *   - `count_view` na samotnom zázname je trvalý súčet. Číta ho všetko, čo radí
 *     alebo zobrazuje „koľkokrát to niekto otvoril", a stojí to jeden stĺpec
 *     bez dopytu navyše.
 *   - tabuľka `views` je len pamäť na to, či ten istý návštevník dnes už bol.
 *     Po 90 dňoch sa preriedi (app:views-prune), takže sa z nej dá čítať len
 *     „za posledných N dní", nie celková história.
 */
trait HasViews
{
    /**
     * Stĺpec s trvalým súčtom. Post ho má z historických dôvodov ako
     * `count_view`, nie `views_count`.
     */
    public function viewsCountColumn(): string
    {
        return 'count_view';
    }

    public function views()
    {
        return $this->morphMany(View::class, 'viewable');
    }

    /**
     * Zoradí podľa počtu zobrazení za posledných N dní — filter „Trend"
     * a výber do newslettera.
     *
     * Počty sa najprv zosumarizujú jedným prechodom cez `views` (grupovaný
     * poddotaz nad views_target_day_index) a až potom pripoja k príspevkom.
     * Korelovaný poddotaz cez withCount() by to isté rátal pre každý
     * z 42-tisíc riadkov zvlášť.
     */
    public function scopeOrderByViewsInPeriod(Builder $query, int $days, string $direction = 'desc'): Builder
    {
        $table = $this->getTable();
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $counts = DB::table('views')
            ->select('viewable_id', DB::raw('count(*) as views_in_period'))
            ->where('viewable_type', $this->getMorphClass())
            ->where('viewed_on', '>=', now()->subDays($days)->toDateString())
            ->groupBy('viewable_id');

        return $query
            // Bez explicitného selectu by stĺpce poddotazu pretiekli do modelu
            // a viewable_id by prepísalo nič netušiaci atribút.
            ->select("{$table}.*")
            ->leftJoinSub($counts, 'period_views', "period_views.viewable_id", '=', "{$table}.id")
            // Príspevok bez zobrazenia v danom okne v poddotaze riadok nemá.
            ->orderByRaw("coalesce(period_views.views_in_period, 0) {$direction}")
            // Drží stránkovanie stabilné, keď má viac príspevkov rovnaký počet.
            ->orderBy("{$table}.id", 'desc');
    }
}
