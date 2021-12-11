<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function show(Organization $organization)
    {
        return new OrganizationResource($organization);
    }
}
