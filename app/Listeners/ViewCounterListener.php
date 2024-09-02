<?php

namespace App\Listeners;


use App\Events\VisitModel;
use App\Services\SessionService;
use App\Services\VisitModels\Counter;
use App\Services\VisitModels\Miles;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ViewCounterListener
{

    protected $miles;


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Miles $miles)
    {
        $this->miles = $miles;
    }

    /**
     * Handle the event.
     *
     * @param  ViewCounter  $event
     * @return void
     */
    public function handle(VisitModel $event)
    {
        if(! $event->model->id) return;
        
        $counter = new Counter($event->model); 
        $counter->handle();

        //        (new SessionService())->counterView($event);

        // delayInSession($minutes)
        views($event->model)->cooldown(60)->record();


        $this->miles->visitingMiles($event->model);
    }
}
