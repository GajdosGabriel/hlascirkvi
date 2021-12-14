<?php

namespace App\Http\Controllers;

use App\User;
use App\Organization;
use Illuminate\Http\Request;

class UserOrganizationController extends Controller
{
    public function index(User $user)
    {
        // dd($user);
        // if(auth()->id() != $user) {
        //    abort(403);
        // }
        $organizations =  $user->organizations()->paginate(30);


        return view('profiles.organizations.index', compact('organizations'));
    }

    public function show(User $user, Organization $organization)
    {
        return view('profiles.organizations.show', [
            'organization' => $organization
        ]);
    }
}
