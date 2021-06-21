<?php

namespace App\Http\Controllers\Events;

use App\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfilEventController extends Controller
{
    public function index()
    {
        $events = Event::whereOrganizationId(auth()->user()->org_id)
                    ->latest()->paginate(30);
                    return view('profiles.events.index', compact('events'));
    }

}
