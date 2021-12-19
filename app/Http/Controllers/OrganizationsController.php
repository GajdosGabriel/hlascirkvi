<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Messenger;
use App\Models\Organization;
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

   
  
  
}
