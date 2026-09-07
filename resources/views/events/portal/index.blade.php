@extends('layouts.events')

@section('title')
    <title>Kresťanské podujatia na Slovensku | Hlas Cirkvi</title>
@endsection

@section('meta')
    <meta name="description"
          content="Prehľad kresťanských podujatí na Slovensku — sväté omše, koncerty, prednášky, duchovné obnovy a púte. Podľa dátumu, mesta aj druhu podujatia.">
    <link rel="canonical" href="{{ route('akcie.index') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Kresťanské podujatia na Slovensku">
    <meta property="og:url" content="{{ route('akcie.index') }}">
@endsection

@section('content')

    @php
        /*
         * Skladanie adries filtrov. Držíme sa toho, čo už v URL je, a meníme
         * len to, na čo návštevník klikol — inak by výber obce zhodil štítky
         * a naopak. Stránkovanie sa pri zmene filtra vždy vracia na začiatok.
         */
        $evUrl = function (array $params) {
            $query = array_merge(request()->query(), $params);
            unset($query['page']);

            $query = array_filter(
                $query,
                static fn ($value) => $value !== null && $value !== '' && $value !== []
            );

            return route('akcie.index') . ($query ? '?' . http_build_query($query) : '');
        };

        /* Štítky sa preklikávajú — kliknutie na zapnutý ho vypne. */
        $evTagUrl = function (string $slug) use ($activeTags, $evUrl) {
            $tags = in_array($slug, $activeTags, true)
                ? array_values(array_diff($activeTags, [$slug]))
                : array_merge($activeTags, [$slug]);

            return $evUrl(['tags' => $tags ? implode(',', $tags) : null]);
        };
    @endphp

    @if ($featured)
        @include('events.portal._hero')
    @endif

    @include('events.portal._filters')

    <div class="mx-auto max-w-6xl px-4 py-8">

        @if ($stale)
            <div class="mb-6 flex items-start gap-3 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
                <i class="fas fa-exclamation-triangle mt-0.5"></i>
                <div>
                    Portál s podujatiami je práve nedostupný, zobrazujeme poslednú načítanú verziu zoznamu.
                </div>
            </div>
        @endif

        <div class="grid gap-10 lg:grid-cols-12">

            <div class="lg:col-span-8">

                {{-- Hlavička výsledkov: čo sa vlastne zobrazuje --}}
                <div class="mb-6 flex flex-wrap items-baseline justify-between gap-2">
                    <h2 class="ev-display text-xl font-semibold">
                        @switch($filters['list'])
                            @case('past') Uplynulé podujatia @break
                            @case('ongoing') Práve prebiehajúce podujatia @break
                            @case('all') Všetky podujatia @break
                            @default
                                {{ $filters['range'] === 'weekend' ? 'Tento víkend' : 'Čo nás čaká' }}
                        @endswitch
                    </h2>

                    @php
                        /*
                         * Slovenčina má tri tvary (1 / 2-4 / 5+). Laravel ich pre
                         * `sk` nerozlišuje — trans_choice vráti pre 39 „podujatia",
                         * preto sa tvar vyberá ručne.
                         */
                        $total = $events->total();
                        $totalLabel = $total === 1
                            ? 'podujatie'
                            : ($total >= 2 && $total <= 4 ? 'podujatia' : 'podujatí');
                    @endphp

                    <span class="text-sm text-stone-500">{{ $total }} {{ $totalLabel }}</span>
                </div>

                @if ($items->isEmpty())
                    <div class="rounded-lg border border-dashed border-[color:var(--ev-line)] bg-white/60 p-10 text-center">
                        <div class="ev-display mb-2 text-lg font-semibold">Nič sa nenašlo</div>
                        <p class="mb-5 text-sm text-stone-500">
                            Skúste zrušiť niektorý z filtrov alebo sa pozrieť do archívu.
                        </p>
                        <a href="{{ route('akcie.index') }}"
                           class="inline-flex items-center gap-2 rounded-md border border-[color:var(--ev-line)] bg-white px-4 py-2 text-sm font-medium transition hover:bg-stone-50">
                            <i class="fas fa-undo text-xs"></i> Zobraziť všetky podujatia
                        </a>
                    </div>

                @elseif ($view === 'mapa')

                    @include('events.portal._map')

                @elseif ($view === 'plagaty')

                    {{-- Stena plagátov --}}
                    <div class="ev-wall">
                        @foreach ($items as $event)
                            @include('events.portal._poster', ['event' => $event])
                        @endforeach
                    </div>

                @else

                    {{-- Časová os: dátumová pečiatka vľavo, podujatia daného dňa vpravo --}}
                    <div class="ev-timeline space-y-8">
                        @foreach ($days as $day => $dayEvents)
                            @php $first = $dayEvents->first(); @endphp

                            <section class="md:flex md:gap-6">

                                <div class="mb-3 shrink-0 md:mb-0 md:w-[6.5rem]">
                                    <div class="ev-stamp inline-flex w-24 flex-col items-center rounded-lg px-3 py-2
                                                {{ $first->isToday() ? 'ev-stamp-today' : '' }}">
                                        <span class="text-[.65rem] uppercase tracking-widest text-stone-500">
                                            {{ $first->dayName() ? mb_substr($first->dayName(), 0, 3) : '—' }}
                                        </span>
                                        <span class="ev-display text-3xl font-bold leading-none">
                                            {{ $first->dayNumber() ?? '?' }}
                                        </span>
                                        <span class="text-[.65rem] uppercase tracking-widest text-stone-500">
                                            {{ $first->monthShort() }}
                                        </span>
                                    </div>

                                    @if ($first->isToday())
                                        <div class="mt-1.5 text-center text-xs font-semibold text-[color:var(--ev-accent)] md:text-left md:pl-1">
                                            dnes
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1 space-y-4">
                                    @foreach ($dayEvents as $event)
                                        @include('events.portal._row', ['event' => $event])
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                @endif

                @if ($events->hasPages())
                    <div class="ev-pagination mt-10">
                        {{-- Filtre si odkazy nesú z `query` nastaveného
                             v EventPortalClient::events(). --}}
                        {{ $events->links() }}
                    </div>
                @endif
            </div>

            <aside class="lg:col-span-4">
                @include('events.portal._aside')
            </aside>
        </div>
    </div>
@endsection
