<?php

namespace App\Http\Controllers;

use App\Messenger;
use App\Organization;
use Illuminate\Http\Request;

class ProfilesController extends Controller
{
    public function profile(Organization $organization)
    {

        //        $this->authorize('update', $user, auth());

        $messages = (new Messenger)->scopeUserMessages($organization->id);

        return view('profiles.home', compact(['organization', 'messages']));
    }
}
