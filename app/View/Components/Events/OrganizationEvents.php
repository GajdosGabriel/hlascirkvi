<?php

namespace App\View\Components\Events;

use Illuminate\View\Component;

class OrganizationEvents extends Component
{
    public $organization;
    public $post;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($organization, $post)
    {
        $this->organization = $organization;
        $this->post = $post;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if($this->organization->person == 0){
            return view('components.events.organization-events');
        }
    }
}
