<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserPrayerController extends Controller
{
    public function index(User $user)
    {
        // $this->authorize('viewAny', $organization);
        
        $prayers = $user->prayers()
            ->latest()->paginate(30);
        return view('profiles.prayers.index', compact('prayers'));
    }
}
