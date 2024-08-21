<?php

namespace App\View\Components\navigation;

use App\Enums\PageType;
use Illuminate\View\Component;

class AsideMenu extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.navigation.aside-menu');
    }

    public function activeMenu()
    {
        if (typePage() == PageType::Profile->value) {
            return $this->userMenu();
        } elseif (typePage() == PageType::Admin->value and auth()->user()->hasRole('admin')) {
            return $this->adminMenu();
        } else {
            return [];
        }
    }

    public function userMenu()
    {
        return [
            [
                'url' => route('profile.profile.index'),
                'icon' => 'home',
                'name' => 'Úvod',
            ],
            [
                'url' => route('profile.user.organization.index', auth()->user()->id),
                'icon' => 'canal',
                'name' => 'Kanály',
            ],
            [
                'url' => route('profile.post.index'),
                'icon' => 'post',
                'name' => 'Články',
            ],
            [
                'url' => route('profile.organization.event.index', auth()->user()->org_id),
                'icon' => 'event',
                'name' => 'Podujatia',
            ],
            [
                'url' => route('profile.organization.seminar.index', auth()->user()->org_id),
                'icon' => 'seminar',
                'name' => 'Semináre',
            ],
            [
                'url' => route('profile.organization.prayer.index', auth()->user()->org_id),
                'icon' => 'pray',
                'name' => 'Modlitby',
            ],
            [
                'url' => route('profile.user.address.index', auth()->id()),
                'icon' => 'contact',
                'name' => 'Moje kontakty',
            ],
            [
                'url' => route('profile.organization.eventSubscribe.index', auth()->user()->org_id),
                'icon' => 'ticket',
                'name' => 'Prihlášky na akcie',
            ],
        ];
    }

    public function adminMenu()
    {
        return [
            [
                'url' => route('admin.home.index'),
                'icon' => 'home',
                'name' => 'Úvod',
            ],
            [
                'url' => route('admin.user.index'),
                'icon' => 'user',
                'name' => 'Užívatelia',
            ],
            [
                'url' => route('admin.organization.index'),
                'icon' => 'canal',
                'name' => 'Kanály',
            ],
            [
                'url' => route('admin.post.index'),
                'icon' => 'post',
                'name' => 'Články',
            ],
            [
                'url' => route('admin.event.index'),
                'icon' => 'event',
                'name' => 'Podujatia',
            ],
            [
                'url' => route('admin.eventSubscribe.index'),
                'icon' => 'ticket',
                'name' => 'Prihlášky na akcie',
            ],
            [
                'url' => route('admin.prayer.index'),
                'icon' => 'pray',
                'name' => 'Modlitby',
            ],
            [
                'url' => route('admin.comment.index'),
                'icon' => 'comment',
                'name' => 'Komentáre',
            ],
            [
                'url' => route('admin.statistic.index'),
                'icon' => 'statistic',
                'name' => 'Štatistika',
            ],
            [
                'url' => route('admin.tag.index'),
                'icon' => 'tag',
                'name' => 'Tagy',
            ],
            [
                'url' => route('admin.updater.index'),
                'icon' => 'statistic',
                'name' => 'Updaters',
            ],
            [
                'url' => route('admin.buffer.index'),
                'icon' => 'buffer',
                'name' => ' Buffer',
            ]
        ];
    }
}
