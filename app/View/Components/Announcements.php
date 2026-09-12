<?php

namespace App\View\Components;

use App\Enums\AnnouncementPlacement;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

/**
 * Vykreslí oznamy pre jedno miesto na stránke: <x-announcements placement="top" />.
 * Keď pre dané miesto nič nebeží, nevypíše sa ani obal — stránka vyzerá presne
 * tak, ako pred zavedením oznamov.
 */
class Announcements extends Component
{
    public ?AnnouncementPlacement $place;

    /** @var Collection<int, Announcement> */
    public Collection $announcements;

    public function __construct(public string $placement)
    {
        $this->place = AnnouncementPlacement::tryFrom($placement);
        $this->announcements = Announcement::visibleFor($placement);
    }

    public function shouldRender(): bool
    {
        return $this->announcements->isNotEmpty();
    }

    public function render()
    {
        return view('components.announcements');
    }
}
