<?php

namespace App\Observers;

use App\Models\BigThink;
use App\Notifications\BigThink\newBigThink;
use App\Models\User;
use Notification;


class BigThinkObserver
{
    /**
     * Handle the big think "created" event.
     *
     * @param  \App\BigThink  $bigThink
     * @return void
     */
    public function created(BigThink $bigThink)
    {
        Notification::send(User::role('admin')->get(), new newBigThink($bigThink));

    }

    /**
     * Handle the big think "updated" event.
     *
     * @param  \App\BigThink  $bigThink
     * @return void
     */
    public function updated(BigThink $bigThink)
    {
        //
    }

    /**
     * Handle the big think "deleted" event.
     *
     * @param  \App\BigThink  $bigThink
     * @return void
     */
    public function deleted(BigThink $bigThink)
    {
        //
    }

    /**
     * Handle the big think "restored" event.
     *
     * @param  \App\BigThink  $bigThink
     * @return void
     */
    public function restored(BigThink $bigThink)
    {
        //
    }

    /**
     * Handle the big think "force deleted" event.
     *
     * @param  \App\BigThink  $bigThink
     * @return void
     */
    public function forceDeleted(BigThink $bigThink)
    {
        //
    }
}
