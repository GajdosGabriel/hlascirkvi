<?php

namespace App\Listeners\User;

use App\User;
use App\Events\User\NotifyBell;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyBellListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(NotifyBell $user)
    {
        $user->model->increment('notify_bell');
    }
}
