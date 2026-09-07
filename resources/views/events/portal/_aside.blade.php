{{-- Bočný panel: kde a čo. Počty prichádzajú z portálu, takže filter ukazuje
     rovno, koľko podujatí sa pod ním nájde. --}}
<div class="space-y-8">

    {{-- Obce podľa počtu podujatí --}}
    @if ($municipalities->isNotEmpty())
        @php
            $topMunicipalities = $municipalities->sortByDesc('events_count')->take(12);
            $maxCount = max(1, (int) $topMunicipalities->max('events_count'));
        @endphp

        <section>
            <h2 class="ev-rule ev-display mb-3 text-sm font-semibold uppercase tracking-wider text-stone-500">
                Kde sa to deje
            </h2>

            <ul class="space-y-1">
                @foreach ($topMunicipalities as $municipality)
                    @php $isActive = ($filters['municipality'] ?? null) === ($municipality['municipality_slug'] ?? null); @endphp
                    <li>
                        <a href="{{ $evUrl(['municipality' => $isActive ? null : ($municipality['municipality_slug'] ?? null)]) }}"
                           class="group relative flex items-center justify-between overflow-hidden rounded-md px-2 py-1.5 text-sm transition
                                  {{ $isActive ? 'bg-amber-100 font-semibold text-amber-900' : 'hover:bg-white' }}">
                            {{-- Prúžok na pozadí namiesto grafu: pomer podujatí
                                 je vidieť bez toho, aby pribudol ďalší prvok. --}}
                            <span class="absolute inset-y-0 left-0 -z-0 bg-[color:var(--ev-paper-deep)] transition-all group-hover:bg-amber-50"
                                  style="width: {{ round(((int) $municipality['events_count'] / $maxCount) * 100) }}%"></span>
                            <span class="relative z-10">{{ $municipality['municipality_name'] ?? '' }}</span>
                            <span class="relative z-10 text-xs text-stone-500">{{ $municipality['events_count'] ?? 0 }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Obsahové štítky, zoskupené tak, ako ich vracia portál --}}
    @if ($tagGroups->isNotEmpty())
        <section>
            <h2 class="ev-rule ev-display mb-3 text-sm font-semibold uppercase tracking-wider text-stone-500">
                Čo hľadáte
            </h2>

            <div class="space-y-4">
                @foreach ($tagGroups as $group)
                    @php
                        $groupTags = collect($group['tags'] ?? [])
                            ->filter(fn ($tag) => ($tag['events_count'] ?? 0) > 0)
                            ->take(14);
                    @endphp

                    @continue($groupTags->isEmpty())

                    <div>
                        <div class="mb-1.5 text-xs uppercase tracking-wider text-stone-400">
                            {{ $group['label'] ?? '' }}
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($groupTags as $tag)
                                @php $isActive = in_array($tag['slug'] ?? '', $activeTags, true); @endphp
                                <a href="{{ $evTagUrl($tag['slug'] ?? '') }}"
                                   class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs transition
                                          {{ $isActive
                                              ? 'border-amber-500 bg-amber-100 font-semibold text-amber-900'
                                              : 'border-[color:var(--ev-line)] bg-white text-stone-600 hover:border-amber-400' }}">
                                    @if (! empty($tag['emoji']))<span>{{ $tag['emoji'] }}</span>@endif
                                    {{ $tag['name'] ?? '' }}
                                    <span class="text-stone-400">{{ $tag['events_count'] ?? 0 }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Podujatia sa zadávajú na portáli, u nás sa len zobrazujú. --}}
    <section class="rounded-lg border border-[color:var(--ev-line)] bg-white p-4">
        <h2 class="ev-display mb-2 text-base font-semibold">Organizujete podujatie?</h2>
        <p class="mb-4 text-sm leading-relaxed text-stone-600">
            Pridajte ho na portál Event — objaví sa aj tu, v tomto výpise.
            Stačí nahrať plagát, ostatné sa doplní samo.
        </p>
        <a href="{{ $portalUrl }}/nahrat-plagat" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 rounded-md bg-[color:var(--ev-night)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-800">
            <i class="fas fa-plus text-xs"></i> Pridať podujatie
        </a>
    </section>

</div>
