<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Filters\EventFilters;
use App\Http\Controllers\Controller;

class EventsController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }

    public function index(EventFilters $filters)
    {
        $events = Event::orderBy('created_at', 'desc')->paginate(30);
        return view('admins.events.index',  compact('events'));
    }
}
