<?php

namespace App\View\Components\Icons;

use Illuminate\View\Component;

class background extends Component
{

    public $name;
    public $title;


    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $title)
    {
        $this->name = $name;
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

    public function bgClass(){

        return request()->input('posts') == $this->name ? 'bg-gray-200' : 'bg-blue-200';

    }

}
