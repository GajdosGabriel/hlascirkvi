<?php

namespace App\Observers;

use App\Event;
use App\Notifications\Admin\NewEvent;
use App\Services\EventImageGenerator;
use App\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;


class EventObserver
{


    /**
     * Handle the event "created" event.
     *
     * @param  \App\Event  $event
     * @return void
     */
    public function created(Event $event)
    {
        if($event->published) {
            (new EventImageGenerator($event))->checkIfEvent();
        }

        session()->flash('flash', 'Podujatie bolo uložené!');

        Notification::send( User::role('admin')->get(), new NewEvent($event) );
    }

    /**
     * Handle the event "updated" event.
     *
     * @param  \App\Event  $event
     * @return void
     */
    public function updated(Event $event)
    {
        if($event->published) {
            $event->images()->whereType('card')->delete();
        (new EventImageGenerator($event))->checkIfEvent();
        }

    }

    /**
     * Handle the event "deleted" event.
     *
     * @param  \App\Event  $event
     * @return void
     */
    public function deleted(Event $event)
    {
        $event->images()->delete();
    }

    /**
     * Handle the event "restored" event.
     *
     * @param  \App\Event  $event
     * @return void
     */
    public function restored(Event $event)
    {
        //
    }

    /**
     * Handle the event "force deleted" event.
     *
     * @param  \App\Event  $event
     * @return void
     */
    public function forceDeleted(Event $event)
    {
        $event->deleteImages();
    }

}
