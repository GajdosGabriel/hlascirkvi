<?php

namespace App\Observers;

use App\Messenger;
use App\Notifications\Messengers\ComingNewMessage;
use App\User;

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

       $user = User::whereId($messenger->requested_user)->first();

//        foreach($messenger->organization as $user)
//        {
            $user->notify(new ComingNewMessage($messenger));
//        }

        session()->flash('flash', 'Správa bola odoslaná!');

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
