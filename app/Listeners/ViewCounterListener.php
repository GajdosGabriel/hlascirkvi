<?php

namespace App\Listeners;


use App\Events\VisitModel;
use App\Services\SessionService;
use App\Notifications\User\CountView;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ViewCounterListener
{


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  ViewCounter  $event
     * @return void
     */
    public function handle(VisitModel $event)
    {
        $event->model->increment('count_view');

//        (new SessionService())->counterView($event);

        // delayInSession($minutes)
        views($event->model)->cooldown(60)->record();


//        // Sending notification email about article reading
//        // Nefunguje organizácia nemá email
//        $visiting = $event->model->count_view;
//        if (  $visiting == 103 ||  $visiting == 305 ||  $visiting == 605 ||  $visiting == 1005
//                AND  $event->model->organization->user->send_email == 1 )
//        {
//            $event->model->organization->user->notify(new CountView($event->model));
//        }


    }
}
