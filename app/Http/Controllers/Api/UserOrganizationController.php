<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationsRequest;
use App\Http\Resources\OrganizationResource;

class UserOrganizationController extends Controller
{
    public function store(User $user, OrganizationsRequest $request)
    {
        $organization = $request->save();
        return new OrganizationResource($organization);
    }
}
