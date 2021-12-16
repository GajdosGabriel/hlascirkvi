<?php

namespace App\Http\Controllers;


use App\Seminar;
use App\Organization;
use Illuminate\Http\Request;

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
        return view('profiles.seminars.show', compact('organization', 'seminar'));
    }


    public function create(Organization $organization)
    {
        $this->authorize('viewAny', $organization);
        return view('profiles.seminars.create', ['seminar' => new Seminar(), 'organization' => $organization]);
    }

    public function edit(Organization $organization, Seminar $seminar)
    {
        $this->authorize('viewAny', $organization);
        return view('seminars.edit', compact('seminar', 'organization'));
    }

    public function store(Organization $organization, Request $request)
    {
        Seminar::create(array_merge($request->all(), ['organization_id' => auth()->user()->org_id]));

        return redirect()->route('organization.seminar.index', $organization->id);
    }

    public function update(Organization $organization, Seminar $seminar, Request $request)
    {
        $this->authorize('update', $seminar);
        $seminar->update($request->all());

        if (request()->expectsJson()) {
            return $seminar;
        };

        return redirect()->route('organization.seminar.index', $organization->id);
    }



    public function destroy(Seminar $seminar)
    {
        $this->authorize('update', $seminar);
        $seminar->posts()->detach();
        $seminar->delete();
        return redirect()->route('seminars.index');
    }
}
