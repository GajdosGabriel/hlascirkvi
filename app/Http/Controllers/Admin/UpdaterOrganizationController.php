<?php

namespace App\Http\Controllers\Admin;

use App\Models\Updater;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
