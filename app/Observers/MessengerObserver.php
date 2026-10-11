<?php

namespace App\Observers;

use App\Models\Messenger;
use App\Notifications\Messengers\ComingNewMessage;
use App\Models\User;
use App\Services\SystemLog\Recorder;

class MessengerObserver
{
    /**
     * Handle the messenger "created" event.
     *
     * @param \App\Messenger $messenger
     * @return void
     */
    public function created(Messenger $messenger)
    {

        $messenger->requestedUser->notify(new ComingNewMessage($messenger));

        session()->flash('flash', 'Správa bola odoslaná!');

        // Text správy je súkromný, do denníka ide len to, že odišla a komu.
        Recorder::activity('message', 'sent', 'Súkromná správa odoslaná', $messenger, (int) $messenger->user_id,
            ['requested_user' => $messenger->requested_user]);

    }

    /**
     * Handle the messenger "updated" event.
     *
     * @param \App\Messenger $messenger
     * @return void
     */
    public function updated(Messenger $messenger)
    {
        //
    }

    /**
     * Handle the messenger "deleted" event.
     *
     * @param \App\Messenger $messenger
     * @return void
     */
    public function deleted(Messenger $messenger)
    {
        //
    }

    /**
     * Handle the messenger "restored" event.
     *
     * @param \App\Messenger $messenger
     * @return void
     */
    public function restored(Messenger $messenger)
    {
        //
    }

    /**
     * Handle the messenger "force deleted" event.
     *
     * @param \App\Messenger $messenger
     * @return void
     */
    public function forceDeleted(Messenger $messenger)
    {
        //
    }
}
