<?php

namespace App\View\Components\Pages;

use Illuminate\View\Component;

class Dashboard extends Component
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
        return view('components.pages.dashboard');
    }

    public function typeMenu(){
        return request()->is('admin/*') ? 'admin_menu' : 'user_menu';
    }
}
