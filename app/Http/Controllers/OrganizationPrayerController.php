<?php

namespace App\Http\Controllers;

use App\Models\Prayer;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Requests\SavePrayerRequest;

class OrganizationPrayerController extends Controller
{
    public function index(Organization $organization)
    {
        // $this->authorize('viewAny', $organization);

        $prayers = $organization->prayers()
            ->latest()->paginate(30);
        return view('profiles.prayers.index', compact('prayers', 'organization'));
    }

    public function create(Organization $organization)
    {
        return view('prayers.create', ['prayer' => new Prayer, 'organization' => $organization]);
    }

    public function edit(Organization $organization, Prayer $prayer)
    {
        return view('prayers.edit', compact('organization', 'prayer'));
    }

    public function update(Organization $organization, Prayer $prayer, SavePrayerRequest $request)
    {
        $prayer->update($request->all());
        return redirect()->route('organization.prayer.index', $organization);
    }

    public function store(Organization $organization, SavePrayerRequest $request)
    {
        $organization->prayers()->create($request->all());

        return redirect()->route('organization.prayer.index', $organization);
    }

    public function destroy(Organization $organization, Prayer $prayer)
    {
        $prayer->delete();

        return redirect()->route('organization.prayer.index', $organization);
    }
}
