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
        $canalId = auth()->user()->canal_id;

        // Semináre a modlitby visia na aktívnom kanáli. Užívateľ, ktorý žiadny
        // kanál nespravuje, ho nemá — route() bez parametra by zhodilo celú
        // nástenku.
        $canalItems = $canalId === null ? [] : [
            [
                'url' => route('profile.canals.seminars.index', $canalId),
                'icon' => 'seminar',
                'name' => 'Kolekcie a semináre',
            ],
            [
                'url' => route('profile.canals.prayers.index', $canalId),
                'icon' => 'pray',
                'name' => 'Modlitby',
            ],
        ];

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
            ...$canalItems,
        ];
    }

    /**
     * Položky administrácie v skupinách. Trinásť odkazov pod sebou bez
     * členenia sa ťažko hľadalo; `group` vykreslí nad prvou položkou skupiny
     * nadpis (components/navigation/aside-menu).
     */
    public function adminMenu()
    {
        $item = fn (string $route, string $icon, string $name, ?string $group = null) => [
            'url' => route($route),
            'icon' => $icon,
            'name' => $name,
            'group' => $group,
        ];

        return [
            $item('admin.home.index', 'home', 'Úvod'),

            $item('admin.post.index', 'post', 'Články', 'Obsah'),
            $item('admin.seminar.index', 'seminar', 'Kolekcie a semináre', 'Obsah'),
            $item('admin.prayer.index', 'pray', 'Modlitby', 'Obsah'),
            $item('admin.comment.index', 'comment', 'Komentáre', 'Obsah'),
            $item('admin.buffer.index', 'buffer', 'Buffer', 'Obsah'),

            $item('admin.user.index', 'user', 'Užívatelia', 'Ľudia a kanály'),
            $item('admin.canal.index', 'canal', 'Kanály', 'Ľudia a kanály'),

            $item('admin.announcement.index', 'announcement', 'Oznamy', 'Web'),
            // Do 10/2026 „Predný zoznam" — názov nehovoril, že ide o kartu
            // kanálov na úvodnej stránke.
            $item('admin.frontlist.index', 'users', 'Kanály na úvode', 'Web'),

            $item('admin.statistic.index', 'statistic', 'Štatistika', 'Systém'),
            $item('admin.ai.index', 'ai', 'AI zhrnutia', 'Systém'),
            $item('admin.logs.index', 'logs', 'Denník', 'Systém'),
        ];
    }
}
