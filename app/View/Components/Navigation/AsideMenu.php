<?php

namespace App\View\Components\Navigation;

use App\Enums\PageType;
use Illuminate\View\Component;

/**
 * Bočné menu správcu kanála a administrácie.
 *
 * Priečinok sa musí volať `Navigation` s veľkým N: značku <x-navigation.aside-menu />
 * prekladá Blade na triedu App\View\Components\Navigation\AsideMenu (každý úsek
 * cez StudlyCase). Kým sa priečinok volal `navigation`, na Windows to prešlo
 * (cesty sú tam bez ohľadu na veľkosť písmen), ale na produkčnom Linuxe sa
 * trieda nenačítala, Blade vykreslil pohľad ako anonymný komponent — a ten
 * spadol na „Undefined variable $menu". S ním padla každá stránka, ktorá menu
 * vykresľuje, čiže celá administrácia aj nástenka.
 */
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
                'url' => route('admin.announcement.index'),
                'icon' => 'announcement',
                'name' => 'Oznamy',
            ],
            [
                'url' => route('admin.frontlist.index'),
                'icon' => 'users',
                'name' => 'Predný zoznam',
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
                'url' => route('admin.buffer.index'),
                'icon' => 'buffer',
                'name' => ' Buffer',
            ]
        ];
    }
}
