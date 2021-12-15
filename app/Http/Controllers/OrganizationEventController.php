<?php

namespace App\Http\Controllers;

use App\User;
use App\Event;
use App\Organization;
use App\Services\Form;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEventRequest;

class OrganizationEventController extends Controller
{
    public function index(Organization $organization)
    {
        $events = $organization->events()
            ->latest()->paginate(30);
        return view('profiles.events.index', compact('events', 'organization'));
    }

    public function create(Organization $organization)
    {
        return view('events.create', ['event' => new Event(), 'organization' => $organization]);
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

        return redirect()->route('akcie.show', [$event->id]);
    }

    public function store(Organization $organization, StoreEventRequest $request)
    {
        $event = $organization->events()->create($request->except(['picture', 'file']));
        (new Form($event, $this))->handler();
        return redirect()->route('akcie.show', [$event->id]);
    }

    public function destroy(Organization $organization, Event $akcie)
    {
        $this->authorize('update', $akcie);
        $akcie->delete();
        session()->flash('flash', 'Podujatie bolo zmazané!');

        return redirect('akcie');
    }
}
