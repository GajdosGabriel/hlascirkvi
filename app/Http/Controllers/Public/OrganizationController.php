<?php

namespace App\Http\Controllers\Public;

use App\Models\User;
use App\Models\Messenger;
use App\Models\Organization;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Requests\OrganizationsRequest;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\Http\Controllers\Controller;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->organization = new EloquentOrganizationRepository;
    }

    public function show(Organization $organization)
    {
        return view('organizations.index', [
            'posts' => $organization->posts()->latest()->paginate(),
            'organization' => $organization
        ]);
    }

   
  
  
}
