@props(['route', 'badge' => null, 'variant' => 'desktop'])

@php
    $isActive = rtrim(Request::url(), '/') === rtrim($route, '/');

    $classes = $variant === 'mobile'
        ? 'flex items-center gap-3 rounded-md px-3 py-2.5 text-base font-medium transition-colors'
        : 'flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors';

    $classes .= $isActive
        ? ' bg-blue-800 text-white'
        : ' text-blue-100 hover:bg-blue-800 hover:text-white';
@endphp

<a href="{{ $route }}" @if ($isActive) aria-current="page" @endif
    {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}

    @if (!is_null($badge) && $badge > 0)
        <span
            class="ml-auto inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold leading-none text-white">
            {{ $badge }}
        </span>
    @endif
</a>
