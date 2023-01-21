<?php

namespace App\Observers;

use App\Models\EventSubscribe;

class EventSubscribeObserver
{
    /**
     * Handle the EventSubscribe "created" event.
     *
     * @param  \App\Models\EventSubscribe  $eventSubscribe
     * @return void
     */
    public function created(EventSubscribe $eventSubscribe)
    {
        //
    }

    /**
     * Handle the EventSubscribe "updated" event.
     *
     * @param  \App\Models\EventSubscribe  $eventSubscribe
     * @return void
     */
    public function updated(EventSubscribe $eventSubscribe)
    {
        //
    }

    /**
     * Handle the EventSubscribe "deleted" event.
     *
     * @param  \App\Models\EventSubscribe  $eventSubscribe
     * @return void
     */
    public function deleted(EventSubscribe $eventSubscribe)
    {
        //
    }

    /**
     * Handle the EventSubscribe "restored" event.
     *
     * @param  \App\Models\EventSubscribe  $eventSubscribe
     * @return void
     */
    public function restored(EventSubscribe $eventSubscribe)
    {
        //
    }

    /**
     * Handle the EventSubscribe "force deleted" event.
     *
     * @param  \App\Models\EventSubscribe  $eventSubscribe
     * @return void
     */
    public function forceDeleted(EventSubscribe $eventSubscribe)
    {
        //
    }
}
