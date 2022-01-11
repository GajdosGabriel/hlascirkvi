@if (auth()->user()->hasRole('admin'))

    <div class="grid col-span-2  min-h-screen">
        <div class="flex flex-col bg-gray-200">


            <x-profil_menu :url="route('admin.users.index')">
                <x-slot name="title">
                    <i class="fas fa-user mr-2 ml-8"></i>
                    Užívatelia
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.organizations.index')">
                <x-slot name="title">
                    <i class="fas fa-sitemap mr-2 ml-8"></i>
                    Kanály
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.posts.index')">
                <x-slot name="title">
                    <i class="fas fa-copy mr-2 ml-8"></i>
                    Články
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.events.index')">
                <x-slot name="title">
                    <i class="fa fa-share-alt mr-2 ml-8" aria-hidden="true"></i>
                    Podujatia
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.prayers.index')">
                <x-slot name="title">
                    <i class="fas fa-praying-hands mr-2 ml-8"></i>
                    Modlitby
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.statistic', ['days' => 1])">
                <x-slot name="title">
                    <i class="far fa-chart-bar mr-2 ml-8"></i>
                    Štatistika
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('tags.index')">
                <x-slot name="title">
                    <i class="fas fa-tags mr-2 ml-8"></i>
                    Tagy
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('updaters.index')">
                <x-slot name="title">
                    <i class="fas fa-list-ul mr-2 ml-8"></i>
                    Updaters
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.unpublished')">
                <x-slot name="title">
                    <i class="fab fa-youtube mr-2 ml-8"></i>
                    Buffer
                </x-slot>
            </x-profil_menu>

        </div>
    </div>


@endif
