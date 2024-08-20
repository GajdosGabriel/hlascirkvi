<?php

namespace App\Http\Controllers\Organization;

use App\Models\Prayer;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Requests\SavePrayerRequest;
use App\Http\Controllers\Controller;

class OrganizationPrayerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Prayer::class, 'prayer');
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function index(Organization $organization)
    {
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
        return redirect()->route('profile.organization.prayer.index', $organization);
    }

    public function store(Organization $organization, SavePrayerRequest $request)
    {
        $organization->prayers()->create($request->all());

        return redirect()->route('profile.organization.prayer.index', $organization);
    }

    public function destroy(Organization $organization, Prayer $prayer)
    {
        $prayer->delete();

        return back();
    }
}
