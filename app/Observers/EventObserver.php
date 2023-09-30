<?php

namespace App\Observers;

use App\Models\Event;
use App\Notifications\Admin\NewEvent;
use App\Services\EventImageGenerator;
use App\Models\User;
use App\Services\FileService\FileService;
use File;
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

        // 271 Výveska
        if ($event->organization_id != 271) {
            // Notification::send(User::role('admin')->get(), new NewEvent($event));
        }
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

            if ($event->isDirty([ 'title', 'start_at', 'end_at', 'village_id'])) {


                foreach ($event->images()->whereType('card')->get() as $image) {
                    $fileService = new FileService($event, $image);
                    $fileService->destroy($image);
                }

                $event->images()->whereType('card')->delete();

                // (new EventImageGenerator($event))->checkIfEvent();
            }
        }

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
