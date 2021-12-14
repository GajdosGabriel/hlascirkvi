<?php

namespace App\Http\Controllers;

use App\User;
use App\Event;
use App\Organization;
use Illuminate\Http\Request;

class OrganizationEventController extends Controller
{
    public function index(Organization $organization)
    {
        $events = $organization->events()
            ->latest()->paginate(30);
        return view('profiles.events.index', compact('events'));
    }
}
