<div class="grid col-span-2  min-h-screen">
    <div class="flex flex-col bg-gray-200">

        <x-profil_menu :url="route('user.organization.index', auth()->user()->id)">
            <x-slot name="title">
                <i class="fas fa-sitemap mr-2 ml-8"></i>
                Kanály
            </x-slot>
        </x-profil_menu>

        <x-profil_menu :url="route('organization.post.index', auth()->user()->org_id)">
            <x-slot name="title">
                <i class="fas fa-copy mr-2 ml-8"></i>
                Články
            </x-slot>
        </x-profil_menu>

        <x-profil_menu :url="route('organization.event.index', auth()->user()->org_id)">
            <x-slot name="title">
                <i class="fa fa-share-alt mr-2 ml-8" aria-hidden="true"></i>
                Podujatia
            </x-slot>
        </x-profil_menu>

        <x-profil_menu :url="route('organization.seminar.index', auth()->user()->org_id)">
            <x-slot name="title">
                <i class="fas fa-chalkboard-teacher mr-2 ml-8"></i>
                Semináre
            </x-slot>
        </x-profil_menu>

        <x-profil_menu :url="route('user.prayer.index', auth()->user()->id)">
            <x-slot name="title">
                <i class="fas fa-praying-hands mr-2 ml-8"></i>
                Modlitby
            </x-slot>
        </x-profil_menu>

        <x-profil_menu :url="route('addresBook.importContacts', [auth()->id(),
            auth()->user()->slug])">
            <x-slot name="title">
                <i class="fas fa-address-card mr-2 ml-8"></i>
                Moje kontakty
            </x-slot>
        </x-profil_menu>

    </div>
</div>
