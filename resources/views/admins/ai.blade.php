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
                Krátke zhrnutie „V skratke“ nad dlhým popisom videa alebo článku. Vytvára ho OpenAI
                (model <code>{{ $model }}</code>) z textu popisu, platí sa za tokeny.
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
                    Cena je odhad podľa cenníka v <code>config/openai.php</code> a počtu tokenov, ktoré vrátilo API.
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
                                    Každú hodinu (o :40) sa spracuje dávka nových príspevkov. Vypnuté = nič sa neminie;
                                    ručné vynútenie nižšie funguje aj tak.
                                </span>
                            </span>
                        </label>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="form-group">
                                <label for="batch">Príspevkov za hodinu</label>
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
                        <p class="text-sm text-gray-500">
                            Vytvorí (alebo prepíše) zhrnutie hneď, aj keď sú automatické zhrnutia vypnuté.
                            Mesačný limit platí. To isté je v menu „Spravovať článok“ na detaile príspevku.
                        </p>
                        <button type="submit" class="ar-btn ar-btn--accent"><i class="fas fa-magic"></i> Vytvoriť zhrnutie</button>
                    </form>
                </x-dashboard.panel>
            </div>

            {{-- Po dňoch --}}
            <x-dashboard.panel title="Posledných 30 dní" class="mt-6">
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
            <x-dashboard.panel title="Posledné zhrnutia" class="mt-6">
                @if ($recent->isEmpty())
                    <p class="text-sm text-gray-500">Zatiaľ žiadne volania.</p>
                @else
                    <x-dashboard.table label="Posledné volania OpenAI">
                        <thead>
                            <tr><th>Kedy</th><th>Príspevok</th><th>Tokeny</th><th>Cena</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $usage)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $usage->created_at?->format('j. n. H:i') }}</td>
                                    <td>
                                        @if ($usage->post)
                                            <a href="{{ route('post.show', [$usage->post->id, $usage->post->slug]) }}" class="underline">
                                                {{ \Illuminate\Support\Str::limit($usage->post->title, 70) }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $num($usage->prompt_tokens + $usage->completion_tokens) }}</td>
                                    <td>{{ $usd($usage->cost_usd) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-dashboard.table>
                @endif
            </x-dashboard.panel>
        </x-slot>
    </x-pages.admin>
@endsection
