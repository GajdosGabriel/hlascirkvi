<?php

namespace App\View\Components\Filters;

use Illuminate\View\Component;

class VideoAvailable extends Component
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
            'admin.posts.index'
        );
        return in_array(\Route::currentRouteName(), $routeName);
    }

    public function name()
    {
        return 'Nedostupné';
    }

    public function url()
    {
        return '?videoAvailable=true';
    }

    public function type()
    {
        return 'videoAvailable';
    }
}
