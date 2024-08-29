<?php

namespace App\Http\Controllers\Public;



use App\Models\Event;
use App\Events\VisitModel;
use Illuminate\Http\Request;
use App\Filters\EventFilters;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\EventRepository;



class EventController extends Controller
{
    protected $event;
    
    public function __construct(EventRepository $event)
    {
        $this->event = $event;
    }


    public function index(EventFilters $filters)
    {
        $events = $this->event->orderByStarting()->filter($filters)->paginate();
        return view('events.index', compact('events'));
    }



    public function create()
    {
        return back();
    }

    public function show(Event $event, $slug)
    {
        // event(new VisitModel($event));

        $commentsLook = $event->comments()->where('type', 'look')->get();
        //        $commentsLook = $event->comments()->where('type', 'look')->get();
        $commentsOffer = $event->comments()->where('type', 'offer')->get();

        return view('events.show', compact('event', 'commentsOffer', 'commentsLook'));
    }
}
