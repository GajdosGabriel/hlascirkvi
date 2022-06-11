<?php

namespace App\View\Components\Icons;

use Illuminate\View\Component;

class Background extends Component
{

    public $requestValue;
    public $title;


    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($requestValue, $title)
    {
        $this->requestValue = $requestValue;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.icons.background');
    }

    public function bgClass()
    {
        return request()->has($this->requestValue) ? 'bg-gray-200' : 'bg-blue-200';
    }
}
