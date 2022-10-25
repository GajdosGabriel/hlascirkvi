<?php

namespace App\Http\Controllers\Events;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\Image;
use App\Services\Form;
use App\Events\VisitModel;
use Illuminate\Http\Request;
use App\Filters\EventFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Repositories\Contracts\EventRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Eloquent\EloquentEventRepository;



class EventController extends Controller
{
    public function __construct(EventRepository $event)
    {
        $this->event = $event;
        $this->middleware('auth')->except('index', 'show', 'finished');
    }

    public function index(EventFilters $filters)
    {
        $events = $this->event->orderByStarting()->filter($filters)->paginate()
            ;
        return view('events.index', compact('events'));
    }

    public function show($event)
    {
        $event = $this->event->find($event);

        event(new VisitModel ($event));

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


    public function printGdpr(Event $event, User $user, $slug)
    {
        $pdf = \PDF::loadView('events.admin.gdpr', compact('user', 'event'));
        return $pdf->stream();
    }


    public function download($filepath = '')
    {
        if ($filepath != '') {
            $file = Image::whereUrl($filepath)->first();
            return response()->file(\Storage::url($file->url));
        } else {
            abort(404);
        }
    }

   
}
