<?php

namespace App\View\Components\Buttons;

use Illuminate\View\Component;

class Fulfilled extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.buttons.fulfilled');
    }

    public function showComponent()
    {
        // Povolené route names.
        $routeName = array(
            'admin.prayers.index'
        );
        return in_array(\Route::currentRouteName(), $routeName);
    }

    public function name()
    {
        return 'Vypočuté';
    }

    public function url()
    {
        return '?fulfilled=true';
    }

    public function type()
    {
        return 'fulfilled';
    }
}
