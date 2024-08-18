
    <div class="grid col-span-2 md:min-h-screen">
        <div class="flex flex-col bg-gray-200">

            @forelse($activeMenu() as $item)
                <x-navigation.aside-menu-item :url="$item['url']">
                    <x-slot name="title">
                        <i class="{{ $item['icon'] }} mr-2 ml-8"></i>
                        {{ $item['name'] }}
                    </x-slot>
                </x-navigation.aside-menu-item>
            @empty
            @endforelse



            {{-- <x-navigation.aside-menu-item :url="route('organization.event.index', auth()->user()->org_id)">
                <x-slot name="title" class="">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 mr-2 ml-8" viewBox="0 0 448 512">
                        <path
                            d="M152 64H296V24C296 10.75 306.7 0 320 0C333.3 0 344 10.75 344 24V64H384C419.3 64 448 92.65 448 128V448C448 483.3 419.3 512 384 512H64C28.65 512 0 483.3 0 448V128C0 92.65 28.65 64 64 64H104V24C104 10.75 114.7 0 128 0C141.3 0 152 10.75 152 24V64zM48 448C48 456.8 55.16 464 64 464H384C392.8 464 400 456.8 400 448V192H48V448z" />
                    </svg>
                    Podujatia
                </x-slot>
            </x-navigation.aside-menu-item> --}}



            {{-- <x-navigation.aside-menu-item :url="route('organization.eventSubscribe.index', auth()->user()->org_id)">
                <x-slot name="title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 mr-2 ml-8" viewBox="0 0 576 512">
                        <path
                            d="M128 160h320v192H128V160zm400 96c0 26.51 21.49 48 48 48v96c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48v-96c26.51 0 48-21.49 48-48s-21.49-48-48-48v-96c0-26.51 21.49-48 48-48h480c26.51 0 48 21.49 48 48v96c-26.51 0-48 21.49-48 48zm-48-104c0-13.255-10.745-24-24-24H120c-13.255 0-24 10.745-24 24v208c0 13.255 10.745 24 24 24h336c13.255 0 24-10.745 24-24V152z" />
                    </svg>
                    Prihlášky na akcie
                </x-slot>
            </x-navigation.aside-menu-item> --}}

        </div>
    </div>

