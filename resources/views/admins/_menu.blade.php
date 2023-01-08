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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 ml-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                      </svg>

                    {{-- <i class="fa fa-share-alt mr-2 ml-8" aria-hidden="true"></i> --}}
                    Podujatia
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.prayers.index')">
                <x-slot name="title">
                    <i class="fas fa-praying-hands mr-2 ml-8"></i>
                    Modlitby
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.comments.index')">
                <x-slot name="title">
                    <i class="far fa-comment-dots mr-2 ml-8"></i>
                    Komentáre
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.statistic.index')">
                <x-slot name="title">
                    <i class="far fa-chart-bar mr-2 ml-8"></i>
                    Štatistika
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.tags.index')">
                <x-slot name="title">
                    <i class="fas fa-tags mr-2 ml-8"></i>
                    Tagy
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.updaters.index')">
                <x-slot name="title">
                    <i class="fas fa-list-ul mr-2 ml-8"></i>
                    Updaters
                </x-slot>
            </x-profil_menu>

            <x-profil_menu :url="route('admin.buffers.index')">
                <x-slot name="title">
                    <i class="fab fa-youtube mr-2 ml-8"></i>
                    Buffer
                </x-slot>
            </x-profil_menu>

        </div>
    </div>


@endif
