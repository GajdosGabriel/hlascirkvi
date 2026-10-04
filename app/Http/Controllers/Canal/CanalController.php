<?php

namespace App\Http\Controllers\Canal;

use App\Enums\CanalIdentityMode;
use App\Enums\CanalSection;
use App\Enums\Denomination;
use App\Models\User;
use App\Models\Canal;
use App\Models\Village;
use App\Filters\CanalFilters;
use App\Http\Requests\CanalRequest;
use App\Http\Controllers\Controller;
use App\Services\FrontList\FrontList;
use App\Services\Youtube\VideoImportSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Kanály prihláseného užívateľa (/dashboard/canals).
 *
 * Do 9/2026 bežalo pod /user/{user}/organization a každá akcia musela
 * overovať, že {user} je prihlásený užívateľ. Teraz sa užívateľ berie výhradne
 * z prihlásenia, takže cudzí profil sa do adresy ani nedá podstrčiť.
 */
class CanalController extends Controller
{
    // Najviac kanálov, ktoré si môže založiť bežný užívateľ.
    public const MAX_CANALS = 3;

    public function __construct()
    {
        $this->middleware('throttle:10,60')->only('store');
    }

    public function create()
    {
        return view('dashboard.canals.create', [
            'villages' => Village::orderBy('fullname')->get(['id', 'fullname', 'zip']),
            'denominations' => Denomination::options(),
            'types' => CanalIdentityMode::options(),
        ]);
    }

    public function store(CanalRequest $request)
    {
        if (! $request->user()->can('admin')
            && $request->user()->canals()->count() >= self::MAX_CANALS) {
            throw ValidationException::withMessages([
                'title' => 'Môžete mať najviac ' . self::MAX_CANALS . ' kanály. Ďalší kanál vám môže založiť administrátor.',
            ]);
        }

        DB::transaction(function () use ($request) {
            $canal = $request->save();

            if ($request->user()->can('superadmin') && $request->boolean('front_listed')) {
                app(FrontList::class)->add($canal);
            }
        });

        return redirect()->route('profile.canals.index')
            ->with('flash', 'Nový kanál bol vytvorený.');
    }

    public function index(Request $request, CanalFilters $filters)
    {
        // Lišta filtrov nad výpisom posiela ?search / ?unpublished / ?deletedAt.
        $canals = $request->user()->canals()
            ->with(['village:id,fullname', 'users:id,first_name,last_name'])
            ->filter($filters)
            ->paginate(30)
            ->withQueryString();

        return view('dashboard.canals.index', compact('canals'));
    }

    public function show(Canal $canal)
    {
        $this->authorize('manage', $canal);

        app(\App\Services\CanalOverview::class)->load($canal);

        return view('dashboard.canals.show', compact('canal'));
    }

    /**
     * Prepnutie aktívneho kanála (canal_id), do ktorého sa zapisujú príspevky.
     *
     * Tlačidlo vo výpise pôvodne posielalo canal_id na admin.user.update, čo je
     * za middleware checkSuperAdmin — bežného správcu kanála teda len ticho
     * presmerovalo na úvodnú stránku. Autorizáciu tu nesie policy `manage`,
     * rovnako ako pri ostatných akciách nad kanálom z nástenky.
     */
    public function switchActive(Request $request, Canal $canal)
    {
        $this->authorize('manage', $canal);

        $request->user()->update(['canal_id' => $canal->id]);

        session()->flash('flash', 'Prepnuté na kanál ' . $canal->title . '.');
        return back();
    }

    public function edit(Request $request, Canal $canal)
    {
        $this->authorize('manage', $canal);

        $canal->load(['users:id,first_name,last_name']);

        return view('dashboard.canals.edit', [
            'canal' => $canal,
            'villages' => Village::orderBy('fullname')->get(['id', 'fullname', 'zip']),
            // Zaradenie, smerovanie videí a deň importu sú dnes stĺpce kanála
            // s pevným číselníkom — netreba pre ne dopyt do databázy.
            'denominations' => Denomination::options(),
            'types' => CanalIdentityMode::options(),
            'sections' => CanalSection::options(),
            'importDays' => Canal::IMPORT_DAYS,
            'suggestedImportDay' => VideoImportSchedule::suggestedDay($canal->id),
            // Zoznam všetkých užívateľov potrebuje len superadmin (výber správcov).
            'users' => $request->user()->can('superadmin')
                ? User::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name'])
                : collect(),
        ]);
    }

    public function update(CanalRequest $request, Canal $canal)
    {
        $this->authorize('manage', $canal);

        $isAdmin = $request->user()->can('admin');

        // YouTube polia, text pred názvom, deň importu a smerovanie videí
        // vykresľuje formulár len adminovi — rovnaký prípad ako
        // `users`/`published` nižšie, stačilo ich doposlať.
        $adminOnly = $isAdmin
            ? []
            : ['youtube_channel', 'youtube_playlist', 'mod_title', 'import_day', 'post_section'];

        $data = collect($request->validated())
            ->except(['users', 'published', 'front_listed', ...$adminOnly])->all();
        if (($data['import_day'] ?? null) === 'auto') {
            $data['import_day'] = VideoImportSchedule::suggestedDay($canal->id);
        }
        $canal->fill($data);
        VideoImportSchedule::assignDayForNewSource($canal);
        if ($canal->isDirty(['import_day', 'youtube_channel', 'youtube_playlist', 'title', 'post_section'])) {
            VideoImportSchedule::reset($canal);
            if ($canal->isDirty(['title', 'youtube_channel', 'youtube_playlist'])) {
                $canal->name_search_completed_until = null;
            }
        }
        $canal->save();

        // Sťahovanie videí sa vypína samo, keď zdroj na YouTube zmizne
        // (App\Services\Youtube\DisableImport). Po zápise iného kanála či
        // playlistu ho treba zapnúť, inak by import ostal ticho vypnutý.
        if ($canal->youtube_disabled_at !== null
            && $canal->wasChanged(['youtube_channel', 'youtube_playlist'])) {
            $canal->update([
                'youtube_disabled_at' => null,
                'youtube_disabled_reason' => null,
            ]);
        }

        // Priradenie správcov kanála a jeho publikovanie sú vo formulári
        // v @can('superadmin') bloku (dashboard/canals/edit.blade.php).
        // Kým to controller nekontroloval, stačilo tie polia doposlať ručne.
        // Bez vybraného správcu sa pole `users` neposiela vôbec; že ide
        // o zámerne prázdny výber, hovorí značka `users_submitted`.
        if ($request->user()->can('superadmin')) {
            if ($request->has('front_listed')) {
                $frontList = app(FrontList::class);
                $request->boolean('front_listed')
                    ? $frontList->add($canal)
                    : $frontList->remove($canal);
            }

            if ($request->has('users') || $request->boolean('users_submitted')) {
                $changes = $canal->users()->sync($request->input('users', []));

                User::whereKey($changes['detached'])->get()
                    ->each->resetActiveCanalIfNotManaged();
            }

            if ($request->has('published')) {
                $canal->update(['published' => $request->validated('published')]);
            }
        }

        // Názov, typ aj zverejnenie sa na kartách predného zoznamu prejavia
        // hneď, nie až po vypršaní cache.
        if ($canal->front_listed_at) {
            app(FrontList::class)->forget();
        }

        session()->flash('flash', 'Údaje boli uložené!');
        return back();
    }

}
