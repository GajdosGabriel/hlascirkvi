
    <div class="grid col-span-2 md:min-h-screen">
        <div class="flex flex-col bg-gray-200">

            @forelse($menu as $item)
                <x-navigation.aside-menu-item :url="$item['url']">
                    <x-slot name="title">
                        @include('components.icons.' . $item['icon'])
                        {{ $item['name'] }}
                    </x-slot>
                </x-navigation.aside-menu-item>
            @empty
            @endforelse

        </div>
    </div>

