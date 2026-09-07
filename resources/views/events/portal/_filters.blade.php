{{-- Lišta filtrov. Drží sa pri hornom okraji, aby sa dalo prepínať aj po
     odrolovaní do polovice zoznamu. --}}
{{-- Pozadie je plná farba, nie priehľadnosť s rozostrením: lišta prekrýva
     obrázky pri rolovaní a blur na nej stál viac výkonu, než koľko pridal. --}}
<div class="sticky top-0 z-30 border-b border-[color:var(--ev-line)] bg-[color:var(--ev-paper)] shadow-sm">
    <div class="mx-auto max-w-6xl px-4 py-3">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Časové zoznamy --}}
            <nav class="flex flex-wrap gap-1 rounded-lg border border-[color:var(--ev-line)] bg-white p-1">
                @php
                    $tabs = [
                        ['label' => 'Najbližšie', 'params' => ['list' => 'upcoming', 'range' => null]],
                        ['label' => 'Tento víkend', 'params' => ['list' => 'upcoming', 'range' => 'weekend']],
                        ['label' => 'Práve prebieha', 'params' => ['list' => 'ongoing', 'range' => null]],
                        ['label' => 'Archív', 'params' => ['list' => 'past', 'range' => null]],
                    ];
                @endphp

                @foreach ($tabs as $tab)
                    @php
                        $isActive = ($filters['list'] ?? 'upcoming') === $tab['params']['list']
                            && ($filters['range'] ?? null) === $tab['params']['range'];
                    @endphp
                    <a href="{{ $evUrl($tab['params']) }}"
                       class="rounded-md px-3 py-1.5 text-sm font-medium transition
                              {{ $isActive
                                  ? 'bg-[color:var(--ev-night)] text-white'
                                  : 'text-stone-600 hover:bg-stone-100' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Hľadanie. Ostatné filtre idú so sebou v skrytých poliach, nech
                 sa vyhľadávaním nezruší vybraná obec ani štítky. --}}
            <form action="{{ route('akcie.index') }}" method="GET" class="flex-1 min-w-[14rem]">
                @foreach (['list', 'municipality', 'tags', 'range'] as $keep)
                    @if (! empty($filters[$keep]))
                        <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
                    @endif
                @endforeach
                @if (($view ?? 'os') !== 'os')
                    <input type="hidden" name="view" value="{{ $view }}">
                @endif

                <label class="relative block">
                    <span class="sr-only">Hľadať podujatie</span>
                    <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-stone-400"></i>
                    <input type="search" name="search" value="{{ $filters['search'] }}"
                           placeholder="Hľadať podujatie, mesto, organizátora…"
                           class="w-full rounded-lg border border-[color:var(--ev-line)] bg-white py-2 pl-9 pr-3 text-sm
                                  placeholder:text-stone-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                </label>
            </form>

            {{-- Podoba výpisu --}}
            <div class="flex rounded-lg border border-[color:var(--ev-line)] bg-white p-1">
                @foreach ([['os', 'Časová os', 'fa-stream'], ['plagaty', 'Plagáty', 'fa-th-large'], ['mapa', 'Mapa', 'fa-map-marked-alt']] as [$key, $label, $icon])
                    <a href="{{ $evUrl(['view' => $key === 'os' ? null : $key]) }}"
                       title="{{ $label }}"
                       class="rounded-md px-3 py-1.5 text-sm transition
                              {{ ($view ?? 'os') === $key
                                  ? 'bg-stone-100 text-stone-900'
                                  : 'text-stone-500 hover:text-stone-800' }}">
                        <i class="fas {{ $icon }}"></i>
                        <span class="ml-1.5 hidden sm:inline">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Zvolené filtre v jednom riadku, každý sa dá zhodiť krížikom. Bez
             tohto je ľahké zabudnúť, prečo je zoznam takmer prázdny. --}}
        @php
            $chips = [];

            if ($filters['search']) {
                $chips[] = ['label' => '„' . $filters['search'] . '"', 'url' => $evUrl(['search' => null])];
            }
            if ($filters['municipality']) {
                $municipalityName = collect($municipalities)
                    ->firstWhere('municipality_slug', $filters['municipality'])['municipality_name']
                    ?? $filters['municipality'];
                $chips[] = ['label' => $municipalityName, 'url' => $evUrl(['municipality' => null])];
            }
            foreach ($activeTags as $tagSlug) {
                $chips[] = ['label' => '#' . $tagSlug, 'url' => $evTagUrl($tagSlug)];
            }
        @endphp

        @if ($chips)
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="text-xs uppercase tracking-wider text-stone-400">Filtre</span>
                @foreach ($chips as $chip)
                    <a href="{{ $chip['url'] }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900 transition hover:bg-amber-200">
                        {{ $chip['label'] }}
                        <i class="fas fa-times text-[10px] opacity-60"></i>
                    </a>
                @endforeach
                <a href="{{ route('akcie.index') }}" class="text-xs text-stone-500 underline hover:text-stone-800">
                    zrušiť všetko
                </a>
            </div>
        @endif
    </div>
</div>
