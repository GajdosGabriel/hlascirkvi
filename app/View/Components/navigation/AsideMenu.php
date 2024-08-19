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

    public function activeMenu()
    {
        if ($this->typeMenu == 'user_menu')
            return $this->userMenu();

        if ($this->typeMenu == 'admin_menu' and auth()->user()->hasRole('admin'))
            return  $this->adminMenu();
    }

    public function userMenu()
    {
        return [
            [
                'url' => route('profile.index'),
                'icon' => 'home',
                'name' => 'Úvod',
            ],
            [
                'url' => route('user.organization.index', auth()->user()->id),
                'icon' => 'canal',
                'name' => 'Kanály',
            ],
            [
                'url' => route('organization.post.index', auth()->user()->org_id),
                'icon' => 'post',
                'name' => 'Články',
            ],
            [
                'url' => route('organization.event.index', auth()->user()->org_id),
                'icon' => 'event',
                'name' => 'Podujatia',
            ],
            [
                'url' => route('organization.seminar.index', auth()->user()->org_id),
                'icon' => 'seminar',
                'name' => 'Semináre',
            ],
            [
                'url' => route('organization.prayer.index', auth()->user()->org_id),
                'icon' => 'pray',
                'name' => 'Modlitby',
            ],
            [
                'url' => route('addresBook.importContacts', [auth()->id(), auth()->user()->slug]),
                'icon' => 'contact',
                'name' => 'Moje kontakty',
            ],
            [
                'url' => route('organization.eventSubscribe.index', auth()->user()->org_id),
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
                'icon' => 'pray',
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
            ],
            [
                'url' => route('addresBook.importContacts', [auth()->id(), auth()->user()->slug]),
                'icon' => 'contact',
                'name' => 'Moje kontakty',
            ],
            [
                'url' => route('admin.eventSubscribe.index'),
                'icon' => 'ticket',
                'name' => 'Prihlášky na akcie',
            ],
        ];
    }
}
