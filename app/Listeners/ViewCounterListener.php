<?php

namespace App\Listeners;

use App\Events\VisitModel;
use App\Services\VisitModels\ViewRecorder;

class ViewCounterListener
{
    protected $recorder;

    public function __construct(ViewRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(VisitModel $event)
    {
        // Poslucháč beží synchrónne v rámci požiadavky na detail príspevku,
        // takže request() je ten, ktorý zobrazenie vyvolal.
        $this->recorder->record($event->model, request());
    }
}
