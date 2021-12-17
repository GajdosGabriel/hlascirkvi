<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\SavePrayerRequest;
use App\Repositories\Eloquent\EloquentUserRepository;

class UserPrayerController extends Controller
{
    public function index(User $user)
    {
        // $this->authorize('viewAny', $organization);

        $prayers = $user->prayers()
            ->latest()->paginate(30);
        return view('profiles.prayers.index', compact('prayers'));
    }

    public function create(User $user)
    {
        return view('profiles.prayers.create', compact('user'));
    }

    public function store(User $user, SavePrayerRequest $request)
    {
        $user->prayers()->create($request->all());

        return redirect()->route('user.prayer.index', $user);
    }
}
