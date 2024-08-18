@props(['route'])

@php
    $classes = Request::url() == $route ? 'border-2 rounded-md px-2' : '';
@endphp
<li class=" whitespace-nowrap">
    <a href="{{ $route }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
</li>
