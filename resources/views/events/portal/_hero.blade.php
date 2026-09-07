{{-- Najbližšie podujatie ako úvodný panel. Tmavý pás s rozostreným plagátom
     na pozadí — na portáli Event je tu obyčajný biely riadok, takže rozdiel
     medzi oboma výpismi je vidieť hneď na prvej obrazovke. --}}
<section class="relative overflow-hidden" style="background: var(--ev-night)">

    {{-- Pozadie je náhľad (320 px) roztiahnutý na celú šírku — je rozmazaný
         sám od seba, takže netreba `filter: blur()` cez celú plochu pásu.
         Zároveň sa ťahá menší súbor než plný plagát. --}}
    @if ($featured->hasPoster())
        <div class="absolute inset-0"
             style="background-image: url('{{ $featured->thumb() ?: $featured->poster() }}');
                    background-size: cover; background-position: center; opacity: .3"></div>
    @endif

    <div class="absolute inset-0"
         style="background: linear-gradient(115deg, rgba(23,35,63,.94) 35%, rgba(23,35,63,.72) 100%)"></div>

    <div class="relative mx-auto max-w-6xl px-4 py-10 md:py-16">

        <h1 class="ev-display text-amber-300 text-sm font-medium uppercase tracking-[.2em] mb-6">
            Podujatia
        </h1>

        <div class="grid gap-8 md:grid-cols-12 md:items-center">

            {{-- Plagát. Bez neho by vľavo ostala diera, preto zástupná dlaždica
                 s dátumom — na stene plagátov sa používa tá istá. --}}
            <div class="md:col-span-4">
                <a href="{{ $featured->url() }}" class="block group">
                    @if ($featured->hasPoster())
                        <img src="{{ $featured->poster() }}"
                             alt="{{ $featured->title() }}"
                             loading="eager"
                             class="w-full rounded-lg shadow-2xl ring-1 ring-white/10 transition duration-300 group-hover:scale-[1.02]">
                    @else
                        <div class="ev-noposter aspect-[3/4] w-full rounded-lg shadow-2xl ring-1 ring-white/10 flex flex-col items-center justify-center">
                            <span class="ev-display text-5xl font-bold text-stone-500">{{ $featured->dayNumber() }}</span>
                            <span class="text-sm uppercase tracking-widest text-stone-500">{{ $featured->monthShort() }}</span>
                        </div>
                    @endif
                </a>
            </div>

            <div class="md:col-span-8 text-white">

                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-stone-900">
                        @if ($featured->isOngoing())
                            <i class="fas fa-circle text-[6px]"></i> Práve prebieha
                        @elseif ($featured->isToday())
                            <i class="fas fa-star text-[10px]"></i> Dnes
                        @elseif ($featured->isTomorrow())
                            Zajtra
                        @else
                            Najbližšie podujatie
                        @endif
                    </span>

                    @if ($featured->dayName())
                        <span class="text-sm text-amber-100/90">
                            {{ ucfirst($featured->dayName()) }}, {{ $featured->longDate() }}
                            @if ($featured->timeLabel())
                                <span class="text-white/60">·</span> {{ $featured->timeLabel() }}
                            @endif
                        </span>
                    @endif
                </div>

                <h2 class="ev-display text-3xl md:text-[2.6rem] leading-[1.15] font-bold mb-4">
                    <a href="{{ $featured->url() }}" class="ev-link">{{ $featured->title() }}</a>
                </h2>

                @if ($featured->excerpt(260))
                    <p class="text-white/75 leading-relaxed mb-6 max-w-2xl">{{ $featured->excerpt(260) }}</p>
                @endif

                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/70 mb-7">
                    @if ($featured->address())
                        <span><i class="fas fa-map-marker-alt mr-2 text-amber-300"></i>{{ $featured->address() }}</span>
                    @endif
                    @if ($featured->organizer())
                        <span><i class="fas fa-church mr-2 text-amber-300"></i>{{ $featured->organizer() }}</span>
                    @endif
                    @if ($featured->price())
                        <span><i class="fas fa-ticket-alt mr-2 text-amber-300"></i>{{ $featured->price() }}</span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ $featured->url() }}"
                       class="inline-flex items-center gap-2 rounded-md bg-amber-400 px-5 py-2.5 font-semibold text-stone-900 transition hover:bg-amber-300">
                        Zobraziť podujatie <i class="fas fa-arrow-right text-sm"></i>
                    </a>

                    @if ($featured->website())
                        <a href="{{ $featured->website() }}" target="_blank" rel="noopener nofollow"
                           class="inline-flex items-center gap-2 rounded-md border border-white/30 px-5 py-2.5 font-medium text-white transition hover:bg-white/10">
                            Prihlásiť sa <i class="fas fa-external-link-alt text-xs"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
