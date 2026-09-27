@extends('layouts.admin')

@section('title')
    <title>AI zhrnutia</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">AI zhrnutia</x-slot>
        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
                // Centy by pri lacnom modeli ukazovali samé nuly, preto viac miest.
                $usd = fn ($value) => '$' . number_format((float) $value, (float) $value < 1 ? 4 : 2, ',', ' ');
                $limitUsed = $limit > 0 ? min(100, (int) round($month->cost / $limit * 100)) : null;
            @endphp

            <p class="mb-6 text-sm text-[color:var(--ar-ink-soft)]">
                Text približne na jednu A4 vytvára OpenAI (model <code>{{ $model }}</code>) výhradne z titulkov videa. Videá bez dostupných titulkov sa preskočia. Popis videa sa nepoužíva.
            </p>

            @unless ($configured)
                <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-900">
                    V <code>.env</code> chýba <code>OPENAI_API_KEY</code> — zhrnutia sa nevytvárajú, ani keď sú zapnuté.
                </div>
            @endunless

            {{-- Spotreba --}}
            <div class="mb-6">
                <p class="ar-kicker mb-3">Spotreba · {{ now()->locale('sk')->isoFormat('MMMM YYYY') }}</p>
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <x-dashboard.metric label="Cena tento mesiac" :value="$usd($month->cost)">
                        @if ($limit > 0)
                            {{ $limitUsed }} % z limitu {{ $usd($limit) }}
                        @else
                            bez limitu
                        @endif
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Tokeny tento mesiac" :value="$num($month->prompt + $month->completion)">
                        {{ $num($month->prompt) }} vstup · {{ $num($month->completion) }} výstup
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Volania tento mesiac" :value="$num($month->calls)">
                        celkovo {{ $num($total->calls) }} za {{ $usd($total->cost) }}
                    </x-dashboard.metric>
                    <x-dashboard.metric label="Priemer na zhrnutie" :value="$averageCost !== null ? $usd($averageCost) : '—'">
                        @if ($averageCost !== null && $waiting > 0)
                            ~{{ $usd($averageCost * $waiting) }} za {{ $num($waiting) }} čakajúcich
                        @else
                            {{ $num($summarized) }} príspevkov má zhrnutie
                        @endif
                    </x-dashboard.metric>
                </div>

                @if ($limitUsed !== null)
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-200" role="progressbar"
                         aria-valuenow="{{ $limitUsed }}" aria-valuemin="0" aria-valuemax="100" aria-label="Čerpanie mesačného limitu">
                        <div class="h-full {{ $limitUsed >= 100 ? 'bg-red-500' : ($limitUsed >= 80 ? 'bg-yellow-500' : 'bg-green-500') }}"
                             style="width: {{ $limitUsed }}%"></div>
                    </div>
                @endif

                <p class="mt-3 text-xs text-gray-500">
                    Cena zahŕňa všetky AI funkcie a odhad poplatkov za vyhľadávanie; vychádza z cenníka v <code>config/openai.php</code> a spotreby API.
                    Zostatok kreditu cez API zistiť nejde — skutočné čerpanie a kredit sú na
                    <a href="https://platform.openai.com/usage" target="_blank" rel="noopener" class="underline">platform.openai.com/usage</a>
                    a <a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank" rel="noopener" class="underline">vyúčtovaní</a>.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Nastavenie --}}
                <x-dashboard.panel title="Automatické zhrnutia">
                    <form method="POST" action="{{ route('admin.ai.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="enabled" value="0">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="enabled" value="1" class="mt-1" @checked($enabled)>
                            <span>
                                <span class="font-semibold">Zapnuté</span>
                                <span class="block text-sm text-gray-500">
                                    Vybrané video z buffera sa skontroluje na zhrnutie ešte pred zverejnením.
                                    Bez titulkov sa zhrnutie nevytvorí. Vypnuté = nič sa neminie;
                                    ručné vynútenie nižšie funguje aj tak.
                                </span>
                            </span>
                        </label>

                        <input type="hidden" name="enrichment_enabled" value="0">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="enrichment_enabled" value="1" class="mt-1" @checked(old('enrichment_enabled', $enrichmentEnabled))>
                            <span>
                                <span class="font-semibold">Dopĺňať organizačné kanály</span>
                                <span class="block text-sm text-gray-500">Po 2 hodinách od vytvorenia vyhľadá overiteľný web, e-mail, telefón, adresu a stručný popis.
                                    Dopĺňa iba prázdne polia; osobné kanály preskočí. Po doplnení informuje správcov e-mailom.
                                    Kontroluje aj staršie profily, po 5 každých 15 minút. Platí spoločný mesačný limit nižšie; vyhľadávanie sa účtuje navyše k tokenom.</span>
                            </span>
                        </label>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-group" hidden>
                                <label for="batch">Príspevkov v doplnkovej hodinovej dávke</label>
                                <input class="form-control" type="number" id="batch" name="batch" min="1" max="200"
                                       value="{{ old('batch', $batch) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="limit">Mesačný limit (USD)</label>
                                <input class="form-control" type="number" id="limit" name="limit" min="0" max="1000" step="0.01"
                                       value="{{ old('limit', $limit) }}" required>
                                <small class="text-gray-500">0 = bez limitu. Po dosiahnutí sa zhrnutia zastavia do konca mesiaca.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="max_tokens">Maximum tokenov titulkov na video</label>
                            <input class="form-control" type="number" id="max_tokens" name="max_tokens" min="1" max="100000"
                                   value="{{ old('max_tokens', $maxTokens) }}" required>
                            <small class="text-gray-500">Predvolene 15 000. Pri dlhšom prepise sa obsahovo vyberú pasáže naprieč celým videom. AI rozvinie najzaujímavejšiu myšlienku z výberu.
                                Používa sa bezpečný horný odhad podľa UTF-8 bajtov, preto môže byť skutočný počet tokenov nižší.
                                Limit platí pre titulky; pokyny a výstup sa účtujú navyše. Automatický výstup je text na A4.</small>
                        </div>

                        <button type="submit" class="ar-btn ar-btn--accent"><i class="fas fa-check"></i> Uložiť</button>
                    </form>
                </x-dashboard.panel>

                {{-- Vynútenie --}}
                <x-dashboard.panel title="Vynútiť zhrnutie príspevku">
                    <form method="POST" action="{{ route('admin.ai.summarize') }}" class="space-y-4">
                        @csrf
                        <div class="form-group">
                            <label for="post">ID alebo adresa príspevku</label>
                            <input class="form-control" type="text" id="post" name="post" required
                                   value="{{ old('post') }}" placeholder="12345 alebo https://www.hlascirkvi.sk/post/12345/…">
                        </div>
                        <div class="form-group">
                            <label for="force_length">Rozsah</label>
                            <select class="form-control" id="force_length" name="length">
                                @foreach ($lengths as $key => $option)
                                    <option value="{{ $key }}" @selected($key === old('length', $length))>
                                        {{ $option['label'] }}{{ $key === $length ? ' — uložené' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-sm text-gray-500">
                            Vytvorí (alebo prepíše) zhrnutie hneď — aj keď sú automatické zhrnutia vypnuté a aj pri
                            krátkych titulkoch. Bez titulkov sa nič nevytvorí. Mesačný limit platí. Rozsah tu platí len pre toto volanie, na skúšanie.
                        </p>
                        <button type="submit" class="ar-btn ar-btn--accent"><i class="fas fa-magic"></i> Vytvoriť zhrnutie</button>
                    </form>

                    @if ($result = session('ai_result'))
                        <div class="mt-6 rounded-lg border border-[color:var(--ar-line)] bg-[color:var(--ar-accent-soft)] p-4">
                            <p class="ar-kicker mb-1 text-[.65rem]">
                                Výsledok · {{ $lengths[$result['length']]['label'] ?? $result['length'] }}
                            </p>
                            <p class="mb-3 text-sm">
                                <a href="{{ $result['url'] }}" target="_blank" rel="noopener" class="font-semibold underline">
                                    {{ \Illuminate\Support\Str::limit($result['title'], 80) }}
                                </a>
                                <span class="text-gray-500">
                                    · {{ ($result['source'] ?? null) === 'captions' ? 'z titulkov videa' : 'z popisu' }},
                                    {{ $num($result['words']) }} slov
                                    @if ($result['tokens'] !== null)
                                        · {{ $num($result['tokens']) }} tokenov · {{ $usd($result['cost']) }}
                                    @endif
                                </span>
                            </p>
                            <div class="text-[.95rem] leading-relaxed">{!! nl2br(e($result['summary'])) !!}</div>
                        </div>
                    @endif
                </x-dashboard.panel>
            </div>

            <x-dashboard.panel title="Dopĺňanie organizačných kanálov" class="mt-6">
                @forelse ($enrichments as $enrichment)
                    <div class="mb-4 border-b border-gray-200 pb-4">
                        <p class="font-semibold">
                            @if ($enrichment->canal)
                                <a href="{{ route('profile.canals.edit', $enrichment->canal) }}" class="underline">{{ $enrichment->canal->title }}</a>
                            @else
                                Odstránený kanál
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">
                            @if ($enrichment->completed_at)
                                {{ $enrichment->changes ? 'Doplnené údaje' : 'Bez doplnenia – údaje sú vyplnené alebo sa nenašiel spoľahlivý zdroj.' }}
                            @elseif ($enrichment->attempts >= 3)
                                Vyhľadávanie zlyhalo po troch pokusoch. Podrobnosti sú v denníku.
                            @elseif ($enrichment->retry_at)
                                Ďalší pokus {{ $enrichment->retry_at->format('j. n. H:i') }}.
                            @else
                                Čaká na spracovanie.
                            @endif
                        </p>
                        @foreach ($enrichment->changes ?? [] as $field => $value)
                            <p class="mt-1 text-sm">
                                <strong>{{ ['url_www' => 'Web', 'email' => 'E-mail', 'phone' => 'Telefón', 'street' => 'Ulica', 'description' => 'Popis'][$field] ?? $field }}:</strong>
                                {{ $value }}
                                @if ($source = $enrichment->evidence[$field]['source_url'] ?? null)
                                    <a href="{{ $source }}" target="_blank" rel="noopener noreferrer" class="underline">Zdroj</a>
                                @endif
                            </p>
                        @endforeach
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Zatiaľ žiadne kontroly profilov.</p>
                @endforelse
            </x-dashboard.panel>
            <x-dashboard.panel title="Prehľad spotreby" class="mt-6" id="spotreba">
                <form method="GET" action="{{ route('admin.ai.index') }}#spotreba" class="grid gap-4 sm:grid-cols-4 mb-6">
                    <div class="form-group">
                        <label for="usage_days">Obdobie</label>
                        <select id="usage_days" name="days" class="form-control">
                            @foreach ([1 => 'Dnes', 7 => 'Posledných 7 dní', 30 => 'Posledných 30 dní', 90 => 'Posledných 90 dní', 365 => 'Posledných 365 dní'] as $value => $label)
                                <option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="usage_feature">Operácia</label>
                        <select id="usage_feature" name="feature" class="form-control">
                            <option value="">Všetky operácie</option>
                            @foreach ($features as $feature)
                                <option value="{{ $feature }}" @selected(($filters['feature'] ?? '') === $feature)>{{ $featureLabels[$feature] ?? $feature }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="usage_model">Model</label>
                        <select id="usage_model" name="model" class="form-control">
                            <option value="">Všetky modely</option>
                            @foreach ($models as $usageModel)
                                <option value="{{ $usageModel }}" @selected(($filters['model'] ?? '') === $usageModel)>{{ $usageModel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-3 pb-4">
                        <button type="submit" class="ar-btn ar-btn--accent">Zobraziť</button>
                        <a href="{{ route('admin.ai.index') }}#spotreba" class="underline">Zrušiť filtre</a>
                    </div>
                </form>
                <p class="mb-4 text-sm text-gray-500">Filtre platia pre nasledujúce prehľady a zoznam volaní. Mesačná spotreba a limit vyššie zahŕňajú všetky operácie.</p>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-dashboard.metric label="Cena za výber" :value="$usd($periodTotals->cost)" />
                    <x-dashboard.metric label="Volania za výber" :value="$num($periodTotals->calls)" />
                    <x-dashboard.metric label="Tokeny za výber" :value="$num($periodTotals->prompt + $periodTotals->completion)">
                        {{ $num($periodTotals->prompt) }} vstup · {{ $num($periodTotals->completion) }} výstup
                    </x-dashboard.metric>
                </div>
            </x-dashboard.panel>

            @foreach ($groups as $column => $group)
                <x-dashboard.panel :title="$group['title']" class="mt-6">
                    <x-dashboard.table :label="$group['title']">
                        <thead><tr><th>Názov</th><th>Volania</th><th>Vstupné tokeny</th><th>Výstupné tokeny</th><th>Cena</th><th>Podiel ceny</th></tr></thead>
                        <tbody>
                            @forelse ($group['rows'] as $row)
                                <tr>
                                    <td>{{ $column === 'feature' ? ($featureLabels[$row->label] ?? $row->label) : $row->label }}</td>
                                    <td>{{ $num($row->calls) }}</td>
                                    <td>{{ $num($row->prompt) }}</td>
                                    <td>{{ $num($row->completion) }}</td>
                                    <td>{{ $usd($row->cost) }}</td>
                                    <td>{{ $periodTotals->cost > 0 ? number_format($row->cost / $periodTotals->cost * 100, 1, ',', ' ') . ' %' : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6">Pre zvolený výber nie sú žiadne volania.</td></tr>
                            @endforelse
                        </tbody>
                    </x-dashboard.table>
                </x-dashboard.panel>
            @endforeach

            <x-dashboard.panel title="Spotreba po dňoch" class="mt-6">
                @if ($daily->isEmpty())
                    <p class="text-sm text-gray-500">Zatiaľ žiadne volania.</p>
                @else
                    <x-dashboard.table label="Spotreba po dňoch">
                        <thead>
                            <tr><th>Deň</th><th>Volania</th><th>Tokeny</th><th>Cena</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($daily as $row)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($row->day)->format('j. n. Y') }}</td>
                                    <td>{{ $num($row->calls) }}</td>
                                    <td>{{ $num($row->tokens) }}</td>
                                    <td>{{ $usd($row->cost) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-dashboard.table>
                @endif
            </x-dashboard.panel>

            {{-- Posledné volania --}}
            <x-dashboard.panel title="AI volania" class="mt-6">
                @if ($recent->isEmpty())
                    <p class="text-sm text-gray-500">Zatiaľ žiadne volania.</p>
                @else
                    <x-dashboard.table label="Posledné volania OpenAI">
                        <thead>
                            <tr><th>Kedy</th><th>Operácia</th><th>Model</th><th>Príspevok</th><th>Tokeny (vstup / výstup)</th><th>Cena</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $usage)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $usage->created_at?->format('j. n. H:i') }}</td>
                                    <td>{{ $featureLabels[$usage->feature] ?? $usage->feature }}</td>
                                    <td>{{ $usage->model }}</td>
                                    <td>
                                        @if ($usage->post)
                                            <a href="{{ route('post.show', [$usage->post->id, $usage->post->slug]) }}" class="underline">
                                                {{ \Illuminate\Support\Str::limit($usage->post->title, 70) }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $num($usage->prompt_tokens) }} / {{ $num($usage->completion_tokens) }}</td>
                                    <td>{{ $usd($usage->cost_usd) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-dashboard.table>
                    <div class="mt-4">{{ $recent->fragment('spotreba')->links() }}</div>
                @endif
            </x-dashboard.panel>
        </x-slot>
    </x-pages.admin>
@endsection
