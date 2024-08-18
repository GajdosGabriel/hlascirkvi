<?php

namespace App\View\Components\navigation;

use Illuminate\View\Component;

class AsideMenu extends Component
{
    public $typeMenu;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($typeMenu)
    {
        $this->typeMenu = $typeMenu;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.navigation.aside-menu');
    }

    public function adminMenu() {
        return [
            [

            ],
        ];
    }

}
