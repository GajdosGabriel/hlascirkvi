@props(['label', 'value'])
<div {{ $attributes->class(['ar-kpi']) }}>
    <span class="ar-kpi__label">{{ $label }}</span>
    <span class="ar-kpi__value">{{ $value }}</span>
    @if (trim((string) $slot) !== '')<span class="ar-kpi__note">{{ $slot }}</span>@endif
</div>
