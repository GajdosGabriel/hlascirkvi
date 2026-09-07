{{-- Dlaždica steny plagátov. Plagáty majú rôznu výšku, preto stĺpcová
     sadzba (.ev-wall) a nie mriežka — obrázky sa nemusia orezávať. --}}
<article class="ev-card group overflow-hidden rounded-lg">
    <a href="{{ $event->url() }}" class="block">
        <div class="relative">
            @if ($event->hasPoster())
                <img data-src="{{ $event->poster() }}"
                     data-sizes="auto"
                     alt="{{ $event->title() }}"
                     class="lazyload w-full">
            @else
                <div class="ev-noposter flex aspect-[3/4] w-full flex-col items-center justify-center">
                    <span class="ev-display text-5xl font-bold">{{ $event->dayNumber() }}</span>
                    <span class="text-xs uppercase tracking-widest">{{ $event->monthShort() }}</span>
                </div>
            @endif

            {{-- Dátum na plagáte — pri stene je to jediný spoločný bod, podľa
                 ktorého sa dá v obrázkoch orientovať. --}}
            <div class="absolute left-3 top-3 rounded-md bg-white/95 px-2.5 py-1 text-center shadow-sm">
                <div class="ev-display text-lg font-bold leading-none">{{ $event->dayNumber() }}</div>
                <div class="text-[.6rem] uppercase tracking-wider text-stone-500">{{ $event->monthShort() }}</div>
            </div>

            @if ($event->isOngoing())
                <span class="absolute right-3 top-3 rounded-full bg-emerald-500 px-2 py-0.5 text-[.65rem] font-semibold uppercase tracking-wide text-white shadow">
                    prebieha
                </span>
            @endif
        </div>
    </a>

    <div class="p-3">
        <h3 class="ev-display text-[.95rem] font-semibold leading-snug">
            <a href="{{ $event->url() }}" class="ev-link">{{ $event->title() }}</a>
        </h3>

        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-stone-500">
            @if ($event->timeLabel())
                <span class="font-medium text-[color:var(--ev-accent)]">{{ $event->timeLabel() }}</span>
            @endif
            @if ($event->municipality())
                <span>{{ $event->municipality() }}</span>
            @endif
        </div>
    </div>
</article>
