<?php

namespace App\View\Components\Events;

use Illuminate\View\Component;
use App\Repositories\Eloquent\EloquentEventRepository;

class ModulCurrentEvents extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(EloquentEventRepository $eloquentEventRepository)
    {
        $this->eloquentEventRepository = $eloquentEventRepository;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if($this->events()->count()) {
            return view('components.events.modul-current-events');
        }
    }

    public function events(){
        return   $this->eloquentEventRepository->curentlyEvents()->get();
    }
}
