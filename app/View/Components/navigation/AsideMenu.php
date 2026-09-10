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
        return view('components.navigation.aside-menu', [
            'menu' => $this->activeMenu()
        ]);
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
                'url' => route('profile.dashboard'),
                'icon' => 'home',
                'name' => 'Dashboard',
            ],
            [
                'url' => route('profile.canals.index'),
                'icon' => 'canal',
                'name' => 'Kanály',
            ],
            [
                'url' => route('profile.posts.index'),
                'icon' => 'post',
                'name' => 'Články',
            ],
            [
                'url' => route('profile.canals.seminars.index', auth()->user()->org_id),
                'icon' => 'seminar',
                'name' => 'Semináre',
            ],
            [
                'url' => route('profile.canals.prayers.index', auth()->user()->org_id),
                'icon' => 'pray',
                'name' => 'Modlitby',
            ],
            [
                'url' => route('profile.user.address.index', auth()->id()),
                'icon' => 'contact',
                'name' => 'Moje kontakty',
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
                'url' => route('admin.canal.index'),
                'icon' => 'canal',
                'name' => 'Kanály',
            ],
            [
                'url' => route('admin.post.index'),
                'icon' => 'post',
                'name' => 'Články',
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
