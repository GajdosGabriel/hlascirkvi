@props(['label'])
<div {{ $attributes->class(['ar-admin__table']) }} role="region" aria-label="{{ $label }}" tabindex="0">
    <table>{{ $slot }}</table>
</div>
