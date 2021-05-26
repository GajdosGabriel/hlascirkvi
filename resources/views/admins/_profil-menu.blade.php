@if (auth()->user()->hasRole('admin'))
    <div class="flex cursor-pointer">

        @component('layouts.components.profil_menu', ['url' => route('admin.organization.index')])
            @slot('title')
                Všetky organizácie
            @endslot
        @endcomponent

        @component('layouts.components.profil_menu', ['url' => route('admin.unpublished')])
            @slot('title')
                Buffer
            @endslot
        @endcomponent

        @component('layouts.components.profil_menu', ['url' => route('admin.statistic', ['days' => 1])])
            @slot('title')
                Štatistika
            @endslot
        @endcomponent

        @component('layouts.components.profil_menu', ['url' => route('admin.user.index')])
            @slot('title')
                Užívatelia
            @endslot
        @endcomponent

    </div>
@endif
