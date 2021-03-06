<?php

namespace App\Http\Controllers;

use App\Filters\PostFilters;
use App\Http\Requests\OrganizationsRequest;
use App\Organization;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\User;
use Illuminate\Http\Request;

class OrganizationsController extends Controller
{
    public function __construct()
    {
        $this->organization = new EloquentOrganizationRepository;
    }

    public function organizationIndex($user)
    {
        if(auth()->id() != $user) {
           abort(403);
        }
        $organizations = $this->organization->usersOrganizations($user);

        return view('organizations.user-index', compact('organizations'));
    }



    public function organizationPosts(Organization $user, $slug)
    {
        return view('posts.index', ['posts' => $user->posts()->latest()->paginate(16), 'organization' => $user] );
    }

    public function edit(Organization $organization, $slug)
    {
        $organization->load('updaters');
        return view('organizations.edit', compact('organization'));
    }


    public function update(Request $request, Organization $organization)
    {
        $organization->update($request->except('updaters'));
        $organization->updaters()->sync($request->get('updaters') ?: []);

        session()->flash('flash', 'Údaje boli uložené!');
        return redirect()->route('organization.index', [auth()->user()->id, auth()->user()->slug]);
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
