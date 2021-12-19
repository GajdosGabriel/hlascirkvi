<?php

namespace App\Http\Controllers\Updaters;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Updater;
use Illuminate\Http\Request;

class UpdaterOrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Updater $updater)
    {
        return view('admins.updater.updater_organizations', compact('updater'));
    }

    public function destroy(Updater $updater, Organization $organization)
    {
        $organization->updaters()->detach($updater->id);
        return back();
    }
}
