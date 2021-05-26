<div class="flex mb-5 cursor-pointer">

    @component('layouts.components.profil_menu', ['url' => route('organization.index', [auth()->id(),
        auth()->user()->slug]) ])
        @slot('title')
            Kanály
        @endslot
    @endcomponent

    @component('layouts.components.profil_menu', ['url' => route('posts.create') ])
        @slot('title')
            Nový článok
        @endslot
    @endcomponent

    @component('layouts.components.profil_menu', ['url' => route('akcie.create') ])
        @slot('title')
            Nové Podujatie
        @endslot
    @endcomponent

    @component('layouts.components.profil_menu', ['url' => route('addresBook.importContacts', [auth()->id(),
        auth()->user()->slug]) ])
        @slot('title')
            Moje kontakty
        @endslot
    @endcomponent

</div>
