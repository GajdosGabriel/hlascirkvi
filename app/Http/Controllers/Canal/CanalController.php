<?php

namespace App\Http\Controllers\Canal;

use App\Models\User;
use App\Models\Canal;
use App\Models\Updater;
use App\Models\Village;
use App\Filters\CanalFilters;
use App\Http\Requests\CanalRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kanály prihláseného užívateľa (/dashboard/canals).
 *
 * Do 9/2026 bežalo pod /user/{user}/organization a každá akcia musela
 * overovať, že {user} je prihlásený užívateľ. Teraz sa užívateľ berie výhradne
 * z prihlásenia, takže cudzí profil sa do adresy ani nedá podstrčiť.
 */
class CanalController extends Controller
{
    /** Skupiny zaradenia, ktoré formulár kanála ponúka. */
    protected const UPDATER_TYPES = ['denomination', 'frontUser', 'post', 'listOfOrganization', 'dayOfWeek'];

    public function create()
    {
        return view('dashboard.canals.create', [
            'villages' => Village::orderBy('fullname')->get(['id', 'fullname', 'zip']),
            'denominations' => Updater::where('type', 'denomination')->orderBy('title')->get(),
        ]);
    }

    public function store(CanalRequest $request)
    {
        DB::transaction(fn () => $request->save());

        return redirect()->route('profile.canals.index')
            ->with('flash', 'Nový kanál bol vytvorený.');
    }

    public function index(Request $request, CanalFilters $filters)
    {
        // Lišta filtrov nad výpisom posiela ?search / ?unpublished / ?deletedAt.
        $canals = $request->user()->organizations()
            ->with(['village:id,fullname', 'updaters:id,title,slug,type', 'users:id,first_name,last_name'])
            ->filter($filters)
            ->paginate(30)
            ->withQueryString();

        return view('dashboard.canals.index', compact('canals'));
    }

    public function show(Canal $canal)
    {
        $this->authorize('manage', $canal);

        return view('dashboard.canals.show', compact('canal'));
    }

    /**
     * Prepnutie aktívneho kanála (org_id), do ktorého sa zapisujú príspevky.
     *
     * Tlačidlo vo výpise pôvodne posielalo org_id na admin.user.update, čo je
     * za middleware checkSuperAdmin — bežného správcu kanála teda len ticho
     * presmerovalo na úvodnú stránku. Autorizáciu tu nesie policy `manage`,
     * rovnako ako pri ostatných akciách nad kanálom z nástenky.
     */
    public function switchActive(Request $request, Canal $canal)
    {
        $this->authorize('manage', $canal);

        $request->user()->update(['org_id' => $canal->id]);

        session()->flash('flash', 'Prepnuté na kanál ' . $canal->title . '.');
        return back();
    }

    public function edit(Request $request, Canal $canal)
    {
        $this->authorize('manage', $canal);

        $canal->load(['updaters', 'users:id,first_name,last_name']);

        return view('dashboard.canals.edit', [
            'canal' => $canal,
            'villages' => Village::orderBy('fullname')->get(['id', 'fullname', 'zip']),
            // Jeden dopyt pre všetky skupiny zaradenia; formulár si ich berie
            // podľa typu. Predtým sa Updater::all() volalo v šablóne päťkrát.
            'updaters' => Updater::whereIn('type', self::UPDATER_TYPES)
                ->orderBy('title')
                ->get(['id', 'title', 'type'])
                ->groupBy('type'),
            // Zoznam všetkých užívateľov potrebuje len superadmin (výber správcov).
            'users' => $request->user()->can('superadmin')
                ? User::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name'])
                : collect(),
        ]);
    }

    public function update(CanalRequest $request, Canal $canal)
    {
        $this->authorize('manage', $canal);

        $isAdmin = $request->user()->hasRole('admin');

        // YouTube polia a text pred názvom vykresľuje formulár len adminovi —
        // rovnaký prípad ako `users`/`published` nižšie, stačilo ich doposlať.
        $adminOnly = $isAdmin ? [] : ['youtube_channel', 'youtube_playlist', 'mod_title'];

        $canal->update(
            collect($request->validated())
                ->except(['updaters', 'users', 'published', ...$adminOnly])
                ->all()
        );

        $canal->updaters()->sync($this->updaterIds($request, $canal, $isAdmin));

        // Priradenie správcov kanála a jeho publikovanie sú vo formulári
        // v @can('superadmin') bloku (dashboard/canals/edit.blade.php).
        // Kým to controller nekontroloval, stačilo tie polia doposlať ručne.
        if ($request->user()->can('superadmin')) {
            if ($request->has('users')) {
                $canal->users()->sync($request->input('users', []));
            }

            if ($request->has('published')) {
                $canal->update(['published' => $request->boolean('published')]);
            }
        }

        session()->flash('flash', 'Údaje boli uložené!');
        return back();
    }

    /**
     * Bežný správca vo formulári vidí len zaradenie (denomination). Kým sa
     * sync robil priamo zo vstupu, každé jeho uloženie zmazalo zaradenia,
     * ktoré kanálu nastavil admin (titulka, zoznamy, dni vyhľadávania).
     */
    protected function updaterIds(CanalRequest $request, Canal $canal, bool $isAdmin): array
    {
        $submitted = collect($request->input('updaters', []))->map(fn ($id) => (int) $id);

        if ($isAdmin) {
            return $submitted->unique()->values()->all();
        }

        $denomination = Updater::where('type', 'denomination')
            ->whereIn('id', $submitted)
            ->pluck('id');

        return $canal->updaters()
            ->where('type', '!=', 'denomination')
            ->pluck('updaters.id')
            ->merge($denomination)
            ->unique()
            ->values()
            ->all();
    }
}
