@props(['heading'])

{{-- Popis stojí pod nadpisom v jednom bloku. Keď bol až za tlačidlami, na
     mobile sa akcie zalomili medzi nadpis a jeho popis. --}}
<header class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
        <h1 class="ar-display break-words text-2xl font-extrabold sm:text-3xl">{{ $heading }}</h1>
        @if (isset($lead) && trim((string) $lead) !== '')
            <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">{{ $lead }}</p>
        @endif
    </div>
    @if (isset($actions) && trim((string) $actions) !== '')
        <div class="ar-dash__new flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</header>
