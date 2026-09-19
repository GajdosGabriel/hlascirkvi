<?php

namespace App\Http\Controllers\Canal;


use App\Models\Seminar;
use App\Models\Canal;
use App\Http\Requests\SaveSeminarRequest;
use App\Http\Controllers\Controller;

class CanalSeminarController extends Controller
{
    public function index(Canal $canal)
    {
        $this->authorize('viewAny', $canal);

        $seminars = $canal->seminars()->withCount('posts')
            ->orderBy('created_at', 'desc')->get();

        return view('profiles.seminars.index', ['seminars' => $seminars, 'canal' => $canal]);
    }

    public function show(Canal $canal, Seminar $seminar)
    {
        $this->authorize('view', $seminar);

        return view('profiles.seminars.show', compact('canal', 'seminar'));
    }


    public function create(Canal $canal)
    {
        // Rovnaká kontrola ako pri uložení — predtým formulár otvoril
        // ktokoľvek a odmietnutie prišlo až po jeho vyplnení.
        $this->authorize('manage', $canal);
        return view('seminars.create', ['seminar' => new Seminar(), 'canal' => $canal]);
    }

    public function edit(Canal $canal, Seminar $seminar)
    {
        $this->authorize('update', $seminar);
        return view('seminars.edit', compact('seminar', 'canal'));
    }

    public function store(Canal $canal, SaveSeminarRequest $request)
    {
        // Autorizácia tu chýbala úplne — a `canal_id` sa bralo z
        // prihláseného užívateľa, nie z routy, takže sa seminár vždy založil
        // pod jeho primárnym kanálom bez ohľadu na to, kde bol formulár.
        //
        // `manage` (zoznam správcov), nie `update` (aktívny kanál): semináre
        // ďalej upravuje a maže každý správca kanála (SeminarPolicy::owns),
        // no založiť ich šlo len v práve aktívnom kanáli — správca iného kanála
        // vyplnil formulár a dostal 403.
        $this->authorize('manage', $canal);

        $canal->seminars()->create($request->validated());

        return redirect()->route('profile.canals.seminars.index', $canal->id);
    }

    public function update(Canal $canal, Seminar $seminar, SaveSeminarRequest $request)
    {
        $this->authorize('update', $seminar);

        $seminar->update($request->validated());

        if (request()->expectsJson()) {
            return $seminar;
        };

        return redirect()->route('profile.canals.seminars.index', $canal->id);
    }



    public function destroy(Canal $canal, Seminar $seminar)
    {
        $this->authorize('delete', $seminar);

        $seminar->posts()->detach();
        $seminar->delete();
        return redirect()->route('konferencie.pute');
    }
}
