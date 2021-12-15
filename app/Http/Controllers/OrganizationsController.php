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


   

  
  
}
