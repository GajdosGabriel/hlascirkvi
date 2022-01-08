<?php

namespace App\Http\Controllers\Api;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;

class OrganizationController extends Controller
{
    public function show(Organization $organization)
    {
        return new OrganizationResource($organization);
    }
}
