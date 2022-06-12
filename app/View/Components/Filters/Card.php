<?php

namespace App\View\Components\Filters;

use Illuminate\View\Component;

class Card extends Component
{
    
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.filters.card');
    }

    public function showComponent()
    {
        // Povolené route names.
       $routeName = array(
            'admin.users.index',
            'admin.posts.index',
            'admin.events.index',
            'admin.comments.index',
       );
        return in_array(\Route::currentRouteName(), $routeName);
    }
}
