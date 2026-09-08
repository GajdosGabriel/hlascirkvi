<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Organization;
use App\Http\Requests\OrganizationsRequest;
use App\Http\Controllers\Controller;

class UserOrganizationController extends Controller
{
    public function index(User $user)
    {
        $this->authorizeUser($user);

        $organizations =  $user->organizations()->paginate(30);

        return view('profiles.organizations.index', compact('organizations'));
    }

    public function show(User $user, Organization $organization)
    {
        $this->authorizeUser($user);
        $this->authorize('manage', $organization);

        return view('profiles.organizations.show', [
            'organization' => $organization,
            'user' => $user
        ]);
    }

    public function edit(User $user, Organization $organization)
    {
        $this->authorizeUser($user);
        $this->authorize('manage', $organization);

        $organization->load('updaters');
        return view('organizations.edit', compact('organization', 'user'));
    }

    public function update(User $user, OrganizationsRequest $request, Organization $organization)
    {
        $this->authorizeUser($user);
        $this->authorize('manage', $organization);

        $organization->update(
            collect($request->validated())->except(['updaters', 'users', 'published'])->all()
        );

        $organization->updaters()->sync($request->input('updaters', []));

        // Priradenie správcov kanála a jeho publikovanie sú vo formulári
        // v @can('superadmin') bloku (organizations/edit.blade.php:82).
        // Kým to controller nekontroloval, stačilo tie polia doposlať ručne.
        if ($request->user()->can('superadmin')) {
            if ($request->has('users')) {
                $organization->users()->sync($request->input('users', []));
            }

            if ($request->has('published')) {
                $organization->update(['published' => $request->boolean('published')]);
            }
        }

        session()->flash('flash', 'Údaje boli uložené!');
        return back();
    }

    /**
     * Routa je vnorená pod /user/{user}, ale {user} sa nikde neoveroval —
     * ktokoľvek prihlásený tak vedel pracovať pod cudzím profilom.
     */
    protected function authorizeUser(User $user): void
    {
        abort_unless(auth()->id() === $user->id, 403);
    }
}
