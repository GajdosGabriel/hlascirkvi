<?php

namespace App\View\Components\Filters;

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
        return view('components.filters.button');
    }

    public function showComponent()
    {
        // Povolené route names.
       $routeName = array(
            'admin.organization.index',
            'admin.event.index',
            'admin.post.index',
            'admin.comment.index',
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
