<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Requests\OrganizationsRequest;

class UserOrganizationController extends Controller
{
    public function index(User $user)
    {
        $organizations =  $user->organizations()->paginate(30);


        return view('profiles.organizations.index', compact('organizations'));
    }

    public function show(User $user, Organization $organization)
    {
        return view('profiles.organizations.show', [
            'organization' => $organization
        ]);
    }

    public function edit(User $user, Organization $organization)
    {
        $organization->load('updaters');
        return view('organizations.edit', compact('organization', 'user'));
    }

    public function update(User $user, Request $request, Organization $organization)
    {
        $organization->update($request->except('updaters'));
        $organization->updaters()->sync($request->get('updaters') ?: []);

        session()->flash('flash', 'Údaje boli uložené!');
        return back();
    }
}
