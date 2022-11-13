<?php

namespace App\Http\Controllers\Profile;

use App\Models\Event;
use App\Services\Form;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Filters\EventFilters;
use App\Http\Requests\StoreEventRequest;
use App\Http\Controllers\Controller;

class OrganizationEventController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function index(Organization $organization, EventFilters $filters)
    {
        $events = $organization->events()->filter($filters)
            ->latest()->paginate(30);
        return view('profiles.events.index', compact('events', 'organization'));
    }

    public function create(Organization $organization)
    {
        return view('events.create', ['event' => new Event(), 'organization' => $organization]);
    }

    public function show(Organization $organization, Event $event)
    {
        $this->authorize('view', $event);
        return view('profiles.events.show', compact('event', 'organization'));
    }

    public function edit(Organization $organization, Event $event)
    {
        $this->authorize('update', $event);
        return view('events.edit', compact('event', 'organization'));
    }

    public function update(Organization $organization, StoreEventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update($request->except(['picture', 'file', 'vizitka']));

        (new Form($event, $request))->handler();

        session()->flash('flash', 'Podujatie je aktualizované!');

        return redirect()->route('organization.event.show', compact('event', 'organization'));
    }

    public function store(Organization $organization, StoreEventRequest $request)
    {
        $event = $organization->events()->create($request->except(['picture', 'file']));
        (new Form($event, $request))->handler();
        session()->flash('flash', 'Podujatie bolo uložené!');
        return redirect()->route('akcie.show', [$event->id]);
    }

    public function destroy(Organization $organization, Event $event)
    {
        $this->authorize('update', $event);
        $event->delete();
        session()->flash('flash', 'Podujatie bolo zmazané!');
        return redirect()->route('organization.event.index', $organization->id);
    }
}
