<?php

namespace App\View\Components\Events\Thumbs;

use Illuminate\View\Component;

class Thumbadmin extends Component
{
    public $event;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.events.thumbs.thumbadmin');
    }
}
