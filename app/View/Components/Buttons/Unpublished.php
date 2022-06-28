<?php

namespace App\View\Components\Buttons;

use Illuminate\View\Component;

class Unpublished extends Component
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
        return view('components.buttons.unpublished');
    }

    public function showComponent()
    {
        // Povolené route names.
       $routeName = array(
            'admin.organizations.index',
            'admin.events.index',
            'admin.posts.index',
            'user.organization.index',
            'organization.post.index',
            'organization.event.index',
       );
        return in_array(\Route::currentRouteName(), $routeName);
    }

    public function name()
    {
        return 'Nepublikované';
    }

    public function url()
    {
        return '?unpublished=true';
    }

    public function type()
    {
        return 'unpublished';
    }
}
