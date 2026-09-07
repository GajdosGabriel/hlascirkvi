<?php

namespace App\Http\Controllers\Organization;


use App\Models\Seminar;
use App\Models\Organization;
use App\Http\Requests\SaveSeminarRequest;
use App\Http\Controllers\Controller;

class OrganizationSeminarController extends Controller
{
    public function index(Organization $organization)
    {
        $this->authorize('viewAny', $organization);

        $seminars = $organization->seminars()->withCount('posts')
            ->orderBy('created_at', 'desc')->get();

        return view('profiles.seminars.index', ['seminars' => $seminars, 'organization' => $organization]);
    }

    public function show(Organization $organization, Seminar $seminar)
    {
        $this->authorize('view', $seminar);

        return view('profiles.seminars.show', compact('organization', 'seminar'));
    }


    public function create(Organization $organization)
    {
        $this->authorize('viewAny', $organization);
        return view('seminars.create', ['seminar' => new Seminar(), 'organization' => $organization]);
    }

    public function edit(Organization $organization, Seminar $seminar)
    {
        $this->authorize('update', $seminar);
        return view('seminars.edit', compact('seminar', 'organization'));
    }

    public function store(Organization $organization, SaveSeminarRequest $request)
    {
        // Autorizácia tu chýbala úplne — a `organization_id` sa bralo z
        // prihláseného užívateľa, nie z routy, takže sa seminár vždy založil
        // pod jeho primárnym kanálom bez ohľadu na to, kde bol formulár.
        $this->authorize('update', $organization);

        $organization->seminars()->create($request->validated());

        return redirect()->route('profile.organization.seminar.index', $organization->id);
    }

    public function update(Organization $organization, Seminar $seminar, SaveSeminarRequest $request)
    {
        $this->authorize('update', $seminar);

        $seminar->update($request->validated());

        if (request()->expectsJson()) {
            return $seminar;
        };

        return redirect()->route('profile.organization.seminar.index', $organization->id);
    }



    public function destroy(Organization $organization, Seminar $seminar)
    {
        $this->authorize('delete', $seminar);

        $seminar->posts()->detach();
        $seminar->delete();
        return redirect()->route('seminars.index');
    }
}
