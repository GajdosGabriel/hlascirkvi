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
            'admin.user.index',
            'admin.post.index',
            'admin.organization.index',
            'admin.event.index',
            'admin.comment.index',
            'admin.prayer.index',
            'user.organization.index',
            'organization.prayer.index',
            'organization.post.index',
            'organization.event.index',
            'event.subscribe.index',
       );
        return in_array(\Route::currentRouteName(), $routeName);
    }
}
