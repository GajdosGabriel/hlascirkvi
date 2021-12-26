<div class="grid col-span-2  min-h-screen">
    <div class="flex flex-col bg-gray-200">

        @component('components.profil_menu', ['url' => route('user.organization.index', auth()->user()->id)])
            @slot('title')
                Kanály
            @endslot
        @endcomponent

        @component('components.profil_menu', ['url' => route('organization.post.index',
            auth()->user()->org_id)])
            @slot('title')
                Články
            @endslot
        @endcomponent

        @component('components.profil_menu', ['url' => route('organization.event.index',
            auth()->user()->org_id)])
            @slot('title')
                Podujatia
            @endslot
        @endcomponent

        @component('components.profil_menu', ['url' => route('organization.seminar.index',
            auth()->user()->org_id)])
            @slot('title')
                Semináre
            @endslot
        @endcomponent

        @component('components.profil_menu', ['url' => route('user.prayer.index', auth()->user()->id)])
            @slot('title')
                Modlitby
            @endslot
        @endcomponent

        @component('components.profil_menu', ['url' => route('addresBook.importContacts', [auth()->id(),
            auth()->user()->slug])])
            @slot('title')
                Moje kontakty
            @endslot
        @endcomponent

    </div>
</div>
