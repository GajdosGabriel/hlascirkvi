<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\SavePrayerRequest;
use App\Prayer;
use App\Repositories\Eloquent\EloquentUserRepository;

class UserPrayerController extends Controller
{
    public function index(User $user)
    {
        // $this->authorize('viewAny', $organization);

        $prayers = $user->prayers()
            ->latest()->paginate(30);
        return view('profiles.prayers.index', compact('prayers', 'user'));
    }

    public function create(User $user)
    {
        return view('prayers.create', ['prayer' => new Prayer, 'user' => $user]);
    }

    public function edit(User $user, Prayer $prayer)
    {
        return view('prayers.edit', compact('user', 'prayer'));
    }

    public function update(User $user, Prayer $prayer, SavePrayerRequest $request)
    {
        $prayer->update($request->all());
        return redirect()->route('user.prayer.index', $user);
    }

    public function store(User $user, SavePrayerRequest $request)
    {
        $user->prayers()->create($request->all());

        return redirect()->route('user.prayer.index', $user);
    }
}
