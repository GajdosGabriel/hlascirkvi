<?php

namespace App\View\Components\Dashboard;

use App\Models\Organization;
use Illuminate\View\Component;

/** Spoločná hlavička stránok kanála v rozložení profilu. */
class Shell extends Component
{
    public function __construct(
        public Organization $organization,
        public string $section = 'dashboard',
        public ?string $heading = null,
    ) {
        $this->heading = $heading ?: $organization->title;
    }

    public function render()
    {
        return view('components.dashboard.shell');
    }
}
