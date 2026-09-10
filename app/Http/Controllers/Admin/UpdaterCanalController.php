<?php

namespace App\Http\Controllers\Admin;

use App\Models\Updater;
use App\Models\Canal;
use App\Http\Controllers\Controller;

class UpdaterCanalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Updater $updater)
    {
        return view('admins.updater.updater_organizations', compact('updater'));
    }

    public function store(Updater $updater, Canal $canal)
    {
        $canal->updaters()->attach($updater->id);
        return back();
    }

    public function destroy(Updater $updater, Canal $canal)
    {
        $canal->updaters()->detach($updater->id);
        return back();
    }
}
