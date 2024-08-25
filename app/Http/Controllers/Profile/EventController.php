<?php

namespace App\Http\Controllers\Profile;

use App\Models\Event;
use App\Services\Files\Form;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Filters\EventFilters;
use App\Http\Requests\StoreEventRequest;
use App\Http\Controllers\Controller;
use App\Services\EventImageGenerator;

class EventController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Event::class, 'event');
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function index(EventFilters $filters)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        $events = $organization->events()->filter($filters)
            ->latest()->paginate(30);
        return view('profiles.events.index', compact('events', 'organization'));
    }

    public function create()
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        return view('events.create', ['event' => new Event(), 'organization' => $organization]);
    }

    public function show(Event $event)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        return view('profiles.events.show', ['post' => $event, 'organization' => $organization]);
    }

    public function edit(Event $event)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        return view('events.edit', compact('event', 'organization'));
    }

    public function update(StoreEventRequest $request, Event $event)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        $event->update($request->except(['pictures', 'file', 'vizitka']));

        (new Form($event, $request))->handler();

        session()->flash('flash', 'Podujatie je aktualizované!');

        return redirect()->route('profile.event.show', compact('event', 'organization'));
    }

    public function store(StoreEventRequest $request)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        $event = $organization->events()->create($request->except(['pictures', 'file']));
        (new Form($event, $request))->handler();
        if ($event->published) {
            if (!$request->pictures) {
                (new EventImageGenerator($event))->checkIfEvent();
            }
        }
        session()->flash('flash', 'Podujatie bolo uložené!');
        return redirect()->route('profile.event.index', [$organization->id, $event->id]);
    }

    public function destroy($event)
    {
        $organization  = Organization::where('id', auth()->user()->org_id)->first();
        $post = Event::withTrashed()->find($event);

        if ($post->deleted_at) {
            $post->restore();
            session()->flash('flash', 'Podujatie bolo obnovené!');
        } else {
            $post->delete();
        }
        session()->flash('flash', 'Podujatie bolo zmazané!');
        return redirect()->route('profile.event.index', $organization->id);
    }
}
