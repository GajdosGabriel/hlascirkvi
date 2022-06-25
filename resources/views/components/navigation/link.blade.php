@props(['published'])

@php
$classes = ($published ?? false)
            ? 'font-semibold border-b-2 border-gray-400'
            : '';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>