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
            <x-slot name="title" class="">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 mr-2 ml-8" viewBox="0 0 448 512">
                    <path
                        d="M152 64H296V24C296 10.75 306.7 0 320 0C333.3 0 344 10.75 344 24V64H384C419.3 64 448 92.65 448 128V448C448 483.3 419.3 512 384 512H64C28.65 512 0 483.3 0 448V128C0 92.65 28.65 64 64 64H104V24C104 10.75 114.7 0 128 0C141.3 0 152 10.75 152 24V64zM48 448C48 456.8 55.16 464 64 464H384C392.8 464 400 456.8 400 448V192H48V448z" />
                </svg>
                Podujatia
            </x-slot>
        </x-profil_menu>

        <x-profil_menu :url="route('organization.seminar.index', auth()->user()->org_id)">
            <x-slot name="title">
                <i class="fas fa-chalkboard-teacher mr-2 ml-8"></i>
                Semináre
            </x-slot>
        </x-profil_menu>

        <x-profil_menu :url="route('organization.prayer.index', auth()->user()->org_id)">
            <x-slot name="title">
                <i class="fas fa-praying-hands mr-2 ml-8"></i>
                Modlitby
            </x-slot>
        </x-profil_menu>

        <x-profil_menu :url="route('addresBook.importContacts', [auth()->id(), auth()->user()->slug])">
            <x-slot name="title">
                <i class="fas fa-address-card mr-2 ml-8"></i>
                Moje kontakty
            </x-slot>
        </x-profil_menu>

    </div>
</div>
