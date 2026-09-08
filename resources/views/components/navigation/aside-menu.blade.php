{{-- Len položky. Obal (šírka, podklad) patrí stránke, ktorá menu vkladá —
     x-pages.dashboard aj nové rozloženie profilu si ho nesú samy. --}}
@forelse($menu as $item)
    <x-navigation.aside-menu-item :url="$item['url']">
        <x-slot name="title">
            @include('components.icons.' . $item['icon'])
            {{ $item['name'] }}
        </x-slot>
    </x-navigation.aside-menu-item>
@empty
@endforelse
