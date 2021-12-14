<?php

namespace App\Http\Controllers;

use App\User;
use App\Organization;
use Illuminate\Http\Request;

class UserOrganizationController extends Controller
{
    public function show(User $user, Organization $organization)
    {
        return view('profiles.organizations.show', [
            'organization' => $organization
        ]);
    }
}
