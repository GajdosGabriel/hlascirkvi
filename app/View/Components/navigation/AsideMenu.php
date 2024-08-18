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
                'icon' => 'fa fa-user',
                'name' => 'Úvod',
            ],
            [
                'url' => route('user.organization.index', auth()->user()->id),
                'icon' => 'fas fa-sitemap',
                'name' => 'Kanály',
            ],
            [
                'url' => route('organization.post.index', auth()->user()->org_id),
                'icon' => 'fas fa-copy',
                'name' => 'Články',
            ],
            [
                'url' => route('organization.event.index', auth()->user()->org_id),
                'icon' => 'fas fa-copy',
                'name' => 'Podujatia',
            ],
            [
                'url' => route('organization.seminar.index', auth()->user()->org_id),
                'icon' => 'fas fa-chalkboard-teacher',
                'name' => 'Semináre',
            ],
            [
                'url' => route('organization.prayer.index', auth()->user()->org_id),
                'icon' => 'fas fa-praying-hands',
                'name' => 'Modlitby',
            ],
            [
                'url' => route('addresBook.importContacts', [auth()->id(), auth()->user()->slug]),
                'icon' => 'fas fa-address-card',
                'name' => 'Moje kontakty',
            ],
            [
                'url' => route('organization.eventSubscribe.index', auth()->user()->org_id),
                'icon' => 'fas fa-address-card',
                'name' => 'Prihlášky na akcie',
            ],
        ];
    }

    public function adminMenu()
    {
        return [
            [
                'url' => route('admin.home.index'),
                'icon' => 'fa fa-user',
                'name' => 'Úvod',
            ],
            [
                'url' => route('admin.user.index'),
                'icon' => 'fas fa-user',
                'name' => 'Užívatelia',
            ],
            [
                'url' => route('admin.organization.index'),
                'icon' => 'fas fa-sitemap',
                'name' => 'Kanály',
            ],
            [
                'url' => route('admin.post.index'),
                'icon' => 'fas fa-copy',
                'name' => 'Články',
            ],
            [
                'url' => route('admin.event.index'),
                'icon' => 'fas fa-copy',
                'name' => 'Podujatia',
            ],
            [
                'url' => route('admin.prayer.index'),
                'icon' => 'fas fa-praying-hands',
                'name' => 'Modlitby',
            ],
            [
                'url' => route('admin.comment.index'),
                'icon' => 'far fa-comment-dots',
                'name' => 'Komentáre',
            ],
            [
                'url' => route('admin.statistic.index'),
                'icon' => 'far fa-chart-bar',
                'name' => 'Štatistika',
            ],
            [
                'url' => route('admin.tag.index'),
                'icon' => 'fas fa-tags',
                'name' => 'Tagy',
            ],
            [
                'url' => route('admin.updater.index'),
                'icon' => 'fas fa-list-ul',
                'name' => 'Updaters',
            ],
            [
                'url' => route('admin.buffer.index'),
                'icon' => 'fab fa-youtube',
                'name' => ' Buffer',
            ],
            [
                'url' => route('addresBook.importContacts', [auth()->id(), auth()->user()->slug]),
                'icon' => 'fas fa-address-card',
                'name' => 'Moje kontakty',
            ],
            [
                'url' => route('admin.eventSubscribe.index'),
                'icon' => 'fas fa-address-card',
                'name' => 'Prihlášky na akcie',
            ],
        ];
    }
}
