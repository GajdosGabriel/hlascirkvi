@props(['heading'])

<header class="mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="ar-display min-w-0 break-words text-2xl font-extrabold sm:text-3xl">{{ $heading }}</h1>
        @if (isset($actions) && trim((string) $actions) !== '')
            <div class="ar-dash__new flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
    @if (isset($lead) && trim((string) $lead) !== '')
        <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">{{ $lead }}</p>
    @endif
</header>
