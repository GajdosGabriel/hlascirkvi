<?php

namespace App\View\Components\Pages;

use Illuminate\View\Component;

class Admin extends Component
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
        return view('components.pages.admin');
    }

    public function typeMenu(){
        return request()->is('admin/*') ? 'admins._profil-menu' : 'profiles._profil-menu';
    }
}
