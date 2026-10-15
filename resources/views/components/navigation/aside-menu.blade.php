{{-- Len položky. Obal (šírka, podklad) patrí stránke, ktorá menu vkladá —
     x-pages.dashboard aj nové rozloženie profilu si ho nesú samy.
     Položka s `group` dostane nad sebou nadpis skupiny, keď sa skupina mení. --}}
@php($group = null)
@foreach ($menu as $item)
    @if (($item['group'] ?? null) !== $group)
        @php($group = $item['group'] ?? null)
        @if ($group)
            <span class="ar-dash__group" aria-hidden="true">{{ $group }}</span>
        @endif
    @endif
    <x-navigation.aside-menu-item :url="$item['url']">
        <x-slot name="title">
            @include('components.icons.' . $item['icon'])
            {{ $item['name'] }}
        </x-slot>
    </x-navigation.aside-menu-item>
@endforeach
