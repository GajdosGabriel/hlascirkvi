<?php

namespace App\View\Components\Dashboard;

use App\Models\Canal;
use Illuminate\View\Component;

/** Spoločná hlavička stránok kanála v rozložení profilu. */
class Shell extends Component
{
    public function __construct(
        public Canal $canal,
        public string $section = 'dashboard',
        public ?string $heading = null,
    ) {
        $this->heading = $heading ?: $canal->title;
    }

    public function render()
    {
        return view('components.dashboard.shell');
    }
}
