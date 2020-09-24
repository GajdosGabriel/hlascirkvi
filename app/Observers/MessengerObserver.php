<?php

namespace App\Observers;

use App\Messenger;
use App\Notifications\Messengers\ComingNewMessage;

class MessengerObserver
{
    /**
     * Handle the messenger "created" event.
     *
     * @param  \App\Messenger  $messenger
     * @return void
     */
    public function created(Messenger $messenger)
    {
        session()->flash('flash', 'Správa bola odoslaná!');

        foreach($messenger->organization as $user)
        {
            $user->notify(new ComingNewMessage($messenger));
        }

    }

    /**
     * Handle the messenger "updated" event.
     *
     * @param  \App\Messenger  $messenger
     * @return void
     */
    public function updated(Messenger $messenger)
    {
        //
    }

    /**
     * Handle the messenger "deleted" event.
     *
     * @param  \App\Messenger  $messenger
     * @return void
     */
    public function deleted(Messenger $messenger)
    {
        //
    }

    /**
     * Handle the messenger "restored" event.
     *
     * @param  \App\Messenger  $messenger
     * @return void
     */
    public function restored(Messenger $messenger)
    {
        //
    }

    /**
     * Handle the messenger "force deleted" event.
     *
     * @param  \App\Messenger  $messenger
     * @return void
     */
    public function forceDeleted(Messenger $messenger)
    {
        //
    }
}
