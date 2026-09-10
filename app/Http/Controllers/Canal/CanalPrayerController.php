<?php

namespace App\Http\Controllers\Canal;

use App\Models\Prayer;
use App\Models\Canal;
use App\Http\Requests\SavePrayerRequest;
use App\Http\Controllers\Controller;

class CanalPrayerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Prayer::class, 'prayer');
        $this->authorizeResource(Canal::class, 'canal');
    }

    public function index(Canal $canal)
    {
        $prayers = $canal->prayers()
            ->latest()->paginate(30);
        return view('profiles.prayers.index', compact('prayers', 'canal'));
    }

    public function create(Canal $canal)
    {
        return view('prayers.create', ['prayer' => new Prayer, 'canal' => $canal]);
    }

    public function edit(Canal $canal, Prayer $prayer)
    {
        return view('prayers.edit', compact('canal', 'prayer'));
    }

    public function update(Canal $canal, Prayer $prayer, SavePrayerRequest $request)
    {
        $prayer->update($request->validated());
        return redirect()->route('profile.canals.prayers.index', $canal);
    }

    public function store(Canal $canal, SavePrayerRequest $request)
    {
        $canal->prayers()->create($request->validated());

        return redirect()->route('profile.canals.prayers.index', $canal);
    }

    public function destroy(Canal $canal, Prayer $prayer)
    {
        $prayer->delete();

        return back();
    }
}
