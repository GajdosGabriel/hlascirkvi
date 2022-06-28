<?php

namespace App\View\Components\Buttons;

use Illuminate\View\Component;

class Banned extends Component
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
        return view('components.buttons.banned');
    }

    public function showComponent()
    {
        // Povolené route names.
       $routeName = array(
            'admin.users.index',
            'admin.organizations.index',
            'user.organization.index',
            'organization.post.index',
            'organization.event.index',
       );
        return in_array(\Route::currentRouteName(), $routeName);
    }

    public function name()
    {
        return 'Banned';
    }

    public function url()
    {
        return '?banned=true';
    }

    public function type()
    {
        return 'banned';
    }
}
