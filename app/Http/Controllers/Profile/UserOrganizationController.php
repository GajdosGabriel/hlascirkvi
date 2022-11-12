<?php

namespace App\Http\Controllers\Profile;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Requests\OrganizationsRequest;
use App\Http\Controllers\Controller;

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
            'organization' => $organization,
            'user' => $user
        ]);
    }

    public function edit(User $user, Organization $organization)
    {
        $organization->load('updaters');
        return view('organizations.edit', compact('organization', 'user'));
    }

    public function update(User $user, Request $request, Organization $organization)
    {
        // dd($request->get('user'));
        $organization->update($request->except(['updaters', 'users']));
        $organization->updaters()->sync($request->get('updaters') ?: []);
        if ($request->get('users')) {
            $organization->users()->sync($request->get('users') ?: []);
        }

        session()->flash('flash', 'Údaje boli uložené!');
        return back();
    }
}
