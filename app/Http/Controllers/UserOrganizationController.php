<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentOrganizationRepository;

class UserOrganizationController extends Controller
{
    public function __construct()
    {
        $this->organization = new EloquentOrganizationRepository;
    }


    public function index(User $user)
    {
        // dd($user);
        // if(auth()->id() != $user) {
        //    abort(403);
        // }
        // $organizations = $this->organization->usersOrganizations($user);
        $organizations = $user->organizations()->paginate(20);

        return view('organizations.user-index', compact('organizations'));
    }
}
