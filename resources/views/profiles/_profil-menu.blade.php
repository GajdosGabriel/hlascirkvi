<div class="grid col-span-2  min-h-screen">
    <div class="flex flex-col bg-gray-200">

        @component('layouts.components.profil_menu', ['url' => route('profile.organizations')])
            @slot('title')
                Kanály
            @endslot
        @endcomponent

        @component('layouts.components.profil_menu', ['url' => route('profile.posts')])
            @slot('title')
                Články
            @endslot
        @endcomponent

        @component('layouts.components.profil_menu', ['url' => route('profile.events')])
            @slot('title')
                Podujatia
            @endslot
        @endcomponent

        @component('layouts.components.profil_menu', ['url' => route('profile.seminars')])
            @slot('title')
                Semináre
            @endslot
        @endcomponent

        @component('layouts.components.profil_menu', ['url' => route('addresBook.importContacts', [auth()->id(),
            auth()->user()->slug])])
            @slot('title')
                Moje kontakty
            @endslot
        @endcomponent

    </div>
</div>
