

    @component('layouts.components.profil_menu', ['url' => route('user.organization.index', auth()->id() ) ])
        @slot('title')
            Kanály
        @endslot
    @endcomponent

    @component('layouts.components.profil_menu', ['url' => route('posts.create') ])
        @slot('title')
            Nový článok
        @endslot
    @endcomponent

    @component('layouts.components.profil_menu', ['url' => route('profil-akcie.index') ])
        @slot('title')
            Podujatia
        @endslot
    @endcomponent

    @component('layouts.components.profil_menu', ['url' => route('addresBook.importContacts', [auth()->id(),
        auth()->user()->slug]) ])
        @slot('title')
            Moje kontakty
        @endslot
    @endcomponent

    @component('layouts.components.profil_menu', ['url' => route('profil-seminars.index') ])
    @slot('title')
        Semináre
    @endslot
@endcomponent





