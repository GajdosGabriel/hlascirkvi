{{-- Podujatie v bočnom paneli titulky: malý plagát, názov, čas a miesto. --}}
<li>
    <a href="{{ $event->url() }}" class="group flex items-start gap-3 rounded-md p-1.5 hover:bg-gray-50">
        @if ($event->hasPoster())
            <img src="{{ $event->thumb() ?: $event->poster() }}"
                 alt="" loading="lazy" width="56" height="56"
                 class="h-14 w-14 shrink-0 rounded object-cover">
        @else
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded bg-gray-100 text-gray-400" aria-hidden="true">
                <i class="ph ph-calendar-blank text-xl"></i>
            </span>
        @endif

        <span class="min-w-0 flex-1">
            <span class="ar-clamp-2 text-sm font-semibold leading-snug text-gray-800 group-hover:text-[color:var(--ar-accent)]">
                {{ $event->title() }}
            </span>

            <span class="mt-0.5 block truncate text-xs text-gray-500">
                @if ($event->timeLabel())
                    {{ $event->timeLabel() }}
                @endif
                @if ($event->timeLabel() && $event->municipality())
                    ·
                @endif
                {{ $event->municipality() }}
            </span>
        </span>
    </a>
</li>
