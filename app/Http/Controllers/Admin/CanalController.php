<?php

namespace App\Http\Controllers\Admin;

use App\Models\Canal;
use Illuminate\Http\Request;
use App\Filters\CanalFilters;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class CanalController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }

    public function index(CanalFilters $filters)
    {
        // Výpis siaha na obec aj správcov kanála (components/canal/list).
        // Bez eager loadu si ich pýtal riadok po riadku — pri 50 kanáloch
        // na stránku to bolo cez 200 dopytov namiesto piatich.
        //
        // Počty a posledný príspevok idú ako podselekty v tom istom dopyte —
        // karta z nich skladá „aktivitu“ kanála. Radenie určuje CanalFilters
        // (predvolene najnovšie registrované).
        $canals = Canal::query()
            ->with(['village:id,fullname', 'users:id,first_name,last_name,email'])
            ->withCount(['posts', 'prayers', 'seminars'])
            ->withMax('posts', 'created_at')
            ->filter($filters)
            ->paginate(50)
            ->withQueryString();

        return view('admins.canals.index', [
            'canals' => $canals,
            'summary' => $this->summary(),
            'registrations' => $this->registrations(),
        ]);
    }

    /**
     * Detail kanála v administrácii. Obsah zdieľa s dashboard/canals/{id}
     * (components/canal/overview), navyše ukazuje admin-details — preto
     * rovnaké počty a vzťahy ako výpis.
     */
    public function show(Canal $canal)
    {
        $canal->load(['village:id,fullname', 'users:id,first_name,last_name,email', 'favorites'])
            ->loadCount(['posts', 'prayers', 'seminars'])
            ->loadMax('posts', 'created_at');

        return view('admins.canals.show', compact('canal'));
    }

    /**
     * Súhrn nad celou tabuľkou (bez zrušených) pre dlaždice nad výpisom.
     * Každé číslo je zároveň odkaz na filter, ktorý tie kanály ukáže.
     */
    private function summary(): object
    {
        $since = now()->subDays(CanalFilters::SILENT_DAYS);

        $row = Canal::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(created_at >= ?) as fresh', [now()->subDays(CanalFilters::FRESH_DAYS)])
            ->selectRaw('SUM(published is null) as unpublished')
            ->selectRaw('SUM(youtube_disabled_at IS NOT NULL) as youtube_off')
            ->whereNull('deleted_at')
            ->first();

        return (object) [
            'total' => (int) $row->total,
            'fresh' => (int) $row->fresh,
            'unpublished' => (int) $row->unpublished,
            'youtubeOff' => (int) $row->youtube_off,
            'orphans' => Canal::doesntHave('users')->count(),
            'silent' => Canal::whereDoesntHave('posts', fn ($q) => $q->where('created_at', '>=', $since))->count(),
        ];
    }

    /**
     * Počet nových kanálov po mesiacoch za posledný rok, od najstaršieho.
     * Mesiace bez registrácie ostávajú v rade s nulou, aby graf nepreskakoval.
     *
     * @return array<int, array{label: string, month: string, count: int}>
     */
    private function registrations(): array
    {
        $start = now()->startOfMonth()->subMonths(11);

        $counts = Canal::withTrashed()
            ->where('created_at', '>=', $start)
            ->pluck('created_at')
            ->countBy(fn (Carbon $date) => $date->format('Y-m'));

        return collect(range(0, 11))
            ->map(function (int $i) use ($start, $counts) {
                $month = $start->copy()->addMonths($i);

                return [
                    'label' => $month->locale('sk')->isoFormat('MMM YYYY'),
                    'month' => $month->format('Y-m'),
                    'count' => (int) $counts->get($month->format('Y-m'), 0),
                ];
            })
            ->all();
    }
}
