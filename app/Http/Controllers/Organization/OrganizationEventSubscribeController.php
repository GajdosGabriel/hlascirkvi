<?php

namespace App\Http\Controllers\Organization;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventSubscribe;
use App\Models\Organization;

class OrganizationEventSubscribeController extends Controller
{
    public function index(Organization $organization)
    {
        $items = $organization->eventSunscribes()->latest()->paginate(40)->withQueryString();
        
        return view('admins.eventSubscribes.index', compact('items'));

    }


}
