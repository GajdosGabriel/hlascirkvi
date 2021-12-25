@if (auth()->user()->hasRole('admin'))

    <div class="grid col-span-2  min-h-screen">
        <div class="flex flex-col bg-gray-200">

            @component('layouts.components.profil_menu', ['url' => route('admin.organizations')])
                @slot('title')
                    Kanály
                @endslot
            @endcomponent

            @component('layouts.components.profil_menu', ['url' => route('admin.users')])
                @slot('title')
                    Užívatelia
                @endslot
            @endcomponent

            @component('layouts.components.profil_menu', ['url' => route('admin.unpublished')])
                @slot('title')
                    Buffer
                @endslot
            @endcomponent

            @component('layouts.components.profil_menu', ['url' => route('admin.index')])
                @slot('title')
                    Podujatia
                @endslot
            @endcomponent

            @component('layouts.components.profil_menu', ['url' => route('admin.statistic', ['days' => 1])])
                @slot('title')
                    Štatistika
                @endslot
            @endcomponent

            @component('layouts.components.profil_menu', ['url' => route('tags.index')])
                @slot('title')
                    Tagy
                @endslot
            @endcomponent

            @component('layouts.components.profil_menu', ['url' => route('updaters.index')])
                @slot('title')
                    Updaters
                @endslot
            @endcomponent

        </div>
    </div>


@endif
