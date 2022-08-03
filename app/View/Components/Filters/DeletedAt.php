<?php

namespace App\View\Components\Filters;

use Illuminate\View\Component;

class DeletedAt extends Component
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
            'admin.prayers.index',
            'admin.comments.index',
            'admin.events.index',
            'admin.posts.index',
            'admin.organizations.index',
            'admin.users.index',
       );
        return in_array(\Route::currentRouteName(), $routeName);
    }

    public function name()
    {
        return 'Vymazané';
    }

    public function url()
    {
        return '?deletedAt=true';
    }

    public function type()
    {
        return 'deletedAt';
    }
}
