@if (auth()->user()->hasRole('admin'))

    <div class="grid col-span-2  min-h-screen">
        <div class="flex flex-col bg-gray-200">


            @component('components.profil_menu', ['url' => route('admin.users.index')])
                @slot('title')
                    <i class="fas fa-user mr-2"></i>
                    Užívatelia
                @endslot
            @endcomponent

            @component('components.profil_menu', ['url' => route('admin.organizations.index')])
                @slot('title')
                    <i class="fas fa-sitemap mr-2"></i>
                    Kanály
                @endslot
            @endcomponent

            @component('components.profil_menu', ['url' => route('admin.unpublished')])
                @slot('title')
                    <i class="fab fa-youtube mr-2"></i>
                    Buffer
                @endslot
            @endcomponent

            @component('components.profil_menu', ['url' => route('admin.events.index')])
                @slot('title')
                    <i class="fa fa-share-alt mr-2" aria-hidden="true"></i>
                    Podujatia
                @endslot
            @endcomponent

            @component('components.profil_menu', ['url' => route('admin.prayers.index')])
                @slot('title')
                    <i class="fas fa-praying-hands mr-2"></i>
                    Modlitby
                @endslot
            @endcomponent

            @component('components.profil_menu', ['url' => route('admin.statistic', ['days' => 1])])
                @slot('title')
                    <i class="far fa-chart-bar mr-2"></i>
                    Štatistika
                @endslot
            @endcomponent

            @component('components.profil_menu', ['url' => route('tags.index')])
                @slot('title')
                    <i class="fas fa-tags mr-2"></i>
                    Tagy
                @endslot
            @endcomponent

            @component('components.profil_menu', ['url' => route('updaters.index')])
                @slot('title')
                    <i class="fas fa-list-ul mr-2"></i>
                    Updaters
                @endslot
            @endcomponent

        </div>
    </div>


@endif
