<?php

namespace App\Http\Controllers;

use App\User;
use App\Messenger;
use App\Organization;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Requests\OrganizationsRequest;
use App\Repositories\Eloquent\EloquentOrganizationRepository;

class OrganizationsController extends Controller
{
    public function __construct()
    {
        $this->organization = new EloquentOrganizationRepository;
    }

    public function show(Organization $organization)
    {
        return view('posts.index', [
            'posts' => $organization->posts()->latest()->paginate(),
            'organization' => $organization
        ]);
    }

    public function organizationPosts(Organization $user, $slug)
    {
        return redirect()->route('organizations.show', [$user->id]);
    }

    public function edit(Organization $organization)
    {
        $organization->load('updaters');
        return view('organizations.edit', compact('organization'));
    }


    public function update(Request $request, Organization $organization)
    {
        $organization->update($request->except('updaters'));
        $organization->updaters()->sync($request->get('updaters') ?: []);

        session()->flash('flash', 'Údaje boli uložené!');
        return back();
    }

    public function store(OrganizationsRequest $request)
    {
        $request->save();

        session()->flash('flash', 'Organizácia bola založená!');

        return back();
    }

    public function delete(Organization $organization)
    {
        $this->authorize('update', $organization);
        $organization->delete();
        session()->flash('flash', 'Kanál bol zmazaný!');
        return back();
    }
}
