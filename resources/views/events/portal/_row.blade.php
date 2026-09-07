{{-- Jedno podujatie v časovej osi: náhľad plagátu vľavo, text vpravo. --}}
<article class="ev-card rounded-lg overflow-hidden">
    <div class="flex flex-col sm:flex-row">

        <a href="{{ $event->url() }}" class="sm:w-40 md:w-44 shrink-0 block group">
            @if ($event->hasPoster())
                <img data-src="{{ $event->thumb() ?: $event->poster() }}"
                     data-sizes="auto"
                     alt="{{ $event->title() }}"
                     class="lazyload h-44 w-full object-cover sm:h-full">
            @else
                <div class="ev-noposter flex h-28 w-full items-center justify-center sm:h-full">
                    <i class="far fa-calendar text-2xl"></i>
                </div>
            @endif
        </a>

        <div class="flex min-w-0 flex-1 flex-col gap-2 p-4">

            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                @if ($event->timeLabel())
                    <span class="font-semibold text-[color:var(--ev-accent)]">
                        <i class="far fa-clock mr-1 text-xs"></i>{{ $event->timeLabel() }}
                    </span>
                @endif

                @if ($event->isOngoing())
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">
                        <i class="fas fa-circle text-[6px]"></i> prebieha
                    </span>
                @endif

                @if ($event->isMultiDay() && $event->endAt())
                    <span class="text-xs text-stone-500">
                        do {{ $event->endAt()->locale('sk')->isoFormat('D. MMMM') }}
                    </span>
                @endif

                @if ($event->seriesUpcomingCount() > 0)
                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">
                        + {{ $event->seriesUpcomingCount() }} ďalších termínov
                    </span>
                @endif
            </div>

            <h3 class="ev-display text-lg font-semibold leading-snug">
                <a href="{{ $event->url() }}" class="ev-link">{{ $event->title() }}</a>
            </h3>

            @if ($event->excerpt(170))
                <p class="text-sm leading-relaxed text-stone-600">{{ $event->excerpt(170) }}</p>
            @endif

            <div class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-1 pt-1 text-xs text-stone-500">
                @if ($event->municipality())
                    <a href="{{ $evUrl(['municipality' => $event->municipalitySlug()]) }}"
                       class="hover:text-[color:var(--ev-accent)]">
                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $event->address() ?: $event->municipality() }}
                    </a>
                @endif

                @if ($event->organizer())
                    <span><i class="fas fa-church mr-1"></i>{{ $event->organizer() }}</span>
                @endif

                @if ($event->isFree())
                    <span class="font-medium text-emerald-700">Vstup voľný</span>
                @elseif ($event->price())
                    <span class="font-medium text-stone-700">{{ $event->price() }}</span>
                @endif
            </div>

            @if ($event->tags())
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach (array_slice($event->tags(), 0, 4) as $tag)
                        <a href="{{ $evTagUrl($tag['slug'] ?? '') }}"
                           class="inline-flex items-center gap-1 rounded-full border border-[color:var(--ev-line)] bg-[color:var(--ev-paper-deep)] px-2 py-0.5 text-[.7rem] text-stone-600 transition hover:border-amber-400 hover:text-amber-800">
                            @if (! empty($tag['emoji']))<span>{{ $tag['emoji'] }}</span>@endif
                            {{ $tag['name'] ?? $tag['slug'] ?? '' }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</article>
