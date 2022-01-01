<?php

namespace App\Observers;

use App\Models\Event;
use App\Notifications\Admin\NewEvent;
use App\Services\EventImageGenerator;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class EventObserver
{


    /**
     * Handle the event "created" event.
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    public function created(Event $event)
    {
        if ($event->published) {
            (new EventImageGenerator($event))->checkIfEvent();
        }


        session()->flash('flash', 'Podujatie bolo uložené!');


        // // 271 Výveska
        // if ($event->organization_id != 271) {
        //     Notification::send(User::role('admin')->get(), new NewEvent($event));
        // }
    }

    /**
     * Handle the event "updated" event.
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    public function updated(Event $event)
    {
        if ($event->published) {
            $event->images()->whereType('card')->delete();
            (new EventImageGenerator($event))->checkIfEvent();
        }

//        if($event->start_at)
//        $event->update(['end_at' => $event->start_at->addHours(2)]);
    }

    /**
     * Handle the event "deleted" event.
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    public function deleted(Event $event)
    {
        $event->images()->delete();
    }

    /**
     * Handle the event "restored" event.
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    public function restored(Event $event)
    {
        //
    }

    /**
     * Handle the event "force deleted" event.
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    public function forceDeleted(Event $event)
    {
        $event->deleteImages();
    }
}
