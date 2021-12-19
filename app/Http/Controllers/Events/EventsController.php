<?php

namespace App\Http\Controllers\Events;

use App\Models\User;
use App\Models\Event;
use App\Models\Image;
use Carbon\Carbon;
use App\Services\Form;
use Illuminate\Http\Request;
use App\Filters\EventFilters;
use App\Events\Posts\ViewCounter;
use App\Http\Requests\StoreEventRequest;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Http\Controllers\Controller;



class EventsController extends Controller
{
    public function __construct()
    {
        $this->event = new EloquentEventRepository;
        $this->middleware('auth')->except('index', 'show', 'finished');
    }

    public function index(EventFilters $filters)
    {
        $events = Event::filter($filters)->orderBy('start_at', 'asc')->paginate(30);
        return view('events.index', compact('events'));
    }

    public function show($event)
    {
        $event = $this->event->find($event);

        event(new ViewCounter($event));

        $commentsLook = $event->comments()->where('type', 'look')->get();
//        $commentsLook = $event->comments()->where('type', 'look')->get();
        $commentsOffer = $event->comments()->where('type', 'offer')->get();

        return view('events.show', compact('event', 'commentsOffer', 'commentsLook'));
    }

    public function eventInfoPanel(Event $event, Request $request)
    {
        $request->validate([
            'ticket_available' => 'numeric|integer|min:0',
            'ticket_staff' => 'numeric|integer|min:0'
        ]);
        $event->update($request->all());
        return back();
    }



    public function adminEvent($event)
    {
        $event = $this->event->find($event);
        $this->authorize('update', $event);
        return view('events.admin.show', compact('event'));
    }

    public function printGdpr(Event $event, User $user, $slug)
    {
        $pdf = \PDF::loadView('events.admin.gdpr', compact('user', 'event'));
        return $pdf->stream();
    }


    public function download ($filepath = '')
    {
        if ($filepath != '') {
            $file = Image::whereUrl($filepath)->first();
            return response()->file( \Storage::url( $file->url) );
        } else {
            abort(404);
        }
    }

    public function finished()
    {
        $events = Event::where('end_at', '<', Carbon::now())->orderBy('start_at', 'desc')->paginate(30);
        $title= 'Ukončené podujatia';
        return view('events.index', compact(['events', 'title']));
    }





}
