<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Organization;
use App\Filters\OrganizationFilters;
use App\Http\Requests\OrganizationsRequest;
use App\Http\Controllers\Controller;

class UserOrganizationController extends Controller
{
    public function create(User $user)
    {
        $this->authorizeUser($user);

        return view('profiles.organizations.create', [
            'user' => $user,
            'villages' => \App\Models\Village::orderBy('fullname')->get(['id', 'fullname', 'zip']),
            'denominations' => \App\Models\Updater::where('type', 'denomination')->orderBy('title')->get(),
        ]);
    }

    public function store(User $user, OrganizationsRequest $request)
    {
        $this->authorizeUser($user);

        \Illuminate\Support\Facades\DB::transaction(fn () => $request->save());

        return redirect()->route('profile.user.organization.index', $user)
            ->with('flash', 'Nový kanál bol vytvorený.');
    }

    public function index(User $user, OrganizationFilters $filters)
    {
        $this->authorizeUser($user);

        // Lišta filtrov nad výpisom posiela ?search / ?unpublished / ?deletedAt.
        // Dovtedy sa tie prepínače kreslili, ale výpis ich nečítal.
        $organizations = $user->organizations()
            ->with(['village:id,fullname', 'updaters:id,title,slug,type', 'users:id,first_name,last_name'])
            ->filter($filters)
            ->paginate(30)
            ->withQueryString();

        return view('profiles.organizations.index', compact('organizations', 'user'));
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

    /**
     * Prepnutie aktívneho kanála (org_id), do ktorého sa zapisujú príspevky.
     *
     * Tlačidlo vo výpise pôvodne posielalo org_id na admin.user.update, čo je
     * za middleware checkSuperAdmin — bežného správcu kanála teda len ticho
     * presmerovalo na úvodnú stránku. Autorizáciu tu nesie policy `manage`,
     * rovnako ako pri ostatných akciách nad kanálom z profilu.
     */
    public function switchActive(User $user, Organization $organization)
    {
        $this->authorizeUser($user);
        $this->authorize('manage', $organization);

        $user->update(['org_id' => $organization->id]);

        session()->flash('flash', 'Prepnuté na kanál ' . $organization->title . '.');
        return back();
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
