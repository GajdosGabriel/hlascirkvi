@extends('layouts.dashboard')

@section('content')

    @if (! $canal)
        {{-- Užívateľ bez prideleného kanála. Nemá zmysel mu ukazovať dlaždice
             s nulami, potrebuje vedieť, kde sa kanál rieši. --}}
        <div class="mx-auto max-w-2xl px-4 py-20 text-center">
            <p class="ar-kicker">Dashboard</p>
            <h1 class="ar-display mt-2 text-2xl font-bold">Zatiaľ nespravujete žiadny kanál</h1>
            <p class="mt-3 text-sm text-gray-500">
                Dashboard ukazuje čísla kanála — zhliadnutia, komentáre, čo čaká na zverejnenie.
                Kým k vášmu účtu žiadny kanál nepatrí, nemá čo zobraziť.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <a href="{{ route('profile.canals.index') }}" class="ar-btn ar-btn--accent">
                    <i class="fas fa-broadcast-tower"></i> Moje kanály
                </a>
                <a href="{{ route('userSupport.index') }}" class="ar-btn ar-btn--quiet">
                    <i class="far fa-envelope"></i> Napísať správcovi
                </a>
            </div>
        </div>
    @else

        @php
            /*
             * Nástenka správcu kanála. Hlavička nesie identitu a rýchle akcie,
             * stred vývoj v čase a to, čo ťahá, bočný panel to, čo si žiada
             * pozornosť (fronta na zverejnenie, komentáre, modlitby).
             */

            $num = fn ($value) => number_format((int) $value, 0, ',', ' ');

            // Milióny zhliadnutí sa do dlaždice nezmestia; pod milión ostáva
            // presné číslo, nad ním stačí rádová hodnota.
            $compact = function ($value) {
                $value = (int) $value;
                return $value >= 1000000
                    ? number_format($value / 1000000, 1, ',', ' ') . ' mil.'
                    : number_format($value, 0, ',', ' ');
            };

            // Slovenčina má pri počtoch tri tvary a v paneloch sa opakujú.
            $plural = function ($count, $one, $few, $many) {
                if ((int) $count === 1) return $one;
                return $count >= 2 && $count <= 4 ? $few : $many;
            };

            $shorten = fn ($text, $length = 90) => \Illuminate\Support\Str::limit(
                trim(strip_tags((string) $text)), $length
            );

            $months = ['jan', 'feb', 'mar', 'apr', 'máj', 'jún',
                       'júl', 'aug', 'sep', 'okt', 'nov', 'dec'];

            $postUrl = fn ($row) => url("/post/{$row->id}/{$row->slug}");
        @endphp

        <x-dashboard.shell :canal="$canal" section="dashboard">

            <x-slot name="lead">
                @if ($posts->last_at)
                    Posledný príspevok {{ \Carbon\Carbon::parse($posts->last_at)->diffForHumans() }}
                    @if ($posts->first_at)
                        <span class="mx-1.5 text-gray-300">·</span>
                        v archíve od {{ \Carbon\Carbon::parse($posts->first_at)->year }}
                    @endif
                @else
                    Kanál zatiaľ nemá žiadny príspevok.
                @endif
            </x-slot>

            <x-slot name="actions">
                <a href="{{ route('profile.canals.posts.create', $canal->id) }}" class="ar-btn ar-btn--accent">
                    <i class="fas fa-plus"></i> Nový článok
                </a>
                <a href="{{ route('organizations.show', $canal->id) }}" class="ar-btn ar-btn--quiet">
                    <i class="far fa-eye"></i> Verejný profil
                </a>
                <a href="{{ route('profile.canals.edit', $canal->id) }}" class="ar-btn ar-btn--quiet">
                    <i class="fas fa-sliders-h"></i> Nastavenia
                </a>
            </x-slot>


            {{-- Jediné, čo si na nástenke pýta zásah: video, ktoré na YouTube
                 už nie je. Preto stojí nad číslami, nie medzi panelmi. --}}
            @if ($posts->broken > 0)
                <div class="ar-alert mb-6">
                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                    <div class="min-w-0">
                        <p class="font-semibold">
                            {{ $num($posts->broken) }}
                            {{ $plural($posts->broken, 'video už nie je dostupné', 'videá už nie sú dostupné', 'videí už nie je dostupných') }}
                            na YouTube
                        </p>
                        <p class="mt-0.5 text-amber-700">
                            Príspevok sa stále zobrazuje, ale prehrávač je prázdny — video bolo na YouTube zmazané
                            alebo skryté.
                        </p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($brokenPosts as $row)
                                <li class="truncate">
                                    <a href="{{ route('profile.canals.posts.edit', [$canal->id, $row->id]) }}"
                                       class="ar-link font-medium">{{ $row->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="mb-6">
                <p class="ar-kicker mb-3">Dnes · {{ $now->format('j. n. Y') }}</p>
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <x-dashboard.metric label="Načítané zo zdrojov dnes" :value="$num($today->imported)">Videá prijaté z YouTube dnes</x-dashboard.metric>
                    <x-dashboard.metric label="Publikované dnes" :value="$num($today->published)">{{ $num($posts->published) }} publikovaných celkovo</x-dashboard.metric>
                    <x-dashboard.metric label="Čakajú na publikovanie" :value="$num($posts->waiting)">Všetky nezverejnené príspevky</x-dashboard.metric>
                    <x-dashboard.metric label="Zhliadnutia dnes" :value="$compact($timeline->last()->views)">Zobrazenia príspevkov kanála</x-dashboard.metric>
                </div>
            </div>

            {{-- ---- Čísla za posledných 30 dní -------------------------- --}}

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

                <x-dashboard.metric label="Zhliadnutia / 30 dní" :value="$compact($views->current)">
                        @include('dashboard._delta', ['change' => $views->change])
                        z {{ $compact($posts->views_total) }} celkovo
                    </x-dashboard.metric>

                <x-dashboard.metric label="Príspevky" :value="$num($posts->total)">
                        @if ($posts->new_window > 0)
                            <span class="font-semibold text-[color:var(--ar-ink)]">+{{ $num($posts->new_window) }}</span>
                            za 30 dní
                        @else
                            za 30 dní nepribudol žiadny
                        @endif
                    </x-dashboard.metric>

                <x-dashboard.metric label="Komentáre / 30 dní" :value="$num($comments->current)">
                        @include('dashboard._delta', ['change' => $comments->change])
                        z {{ $num($comments->total) }} celkovo
                    </x-dashboard.metric>

                <x-dashboard.metric label="Odberatelia" :value="$num($audience->total)">
                        @if ($audience->current > 0)
                            <span class="font-semibold text-[color:var(--ar-ink)]">+{{ $num($audience->current) }}</span>
                            za 30 dní
                        @else
                            za 30 dní bez zmeny
                        @endif
                    </x-dashboard.metric>
            </div>

            {{-- ---- Vývoj v čase a výpisy ------------------------------- --}}

            <div class="mt-6 grid gap-6 lg:grid-cols-12">

                <div class="min-w-0 space-y-6 lg:col-span-8">

                    @include('dashboard._chart')

                    {{-- Čo kanál ťahá práve teraz. Zámerne za posledných 30 dní,
                         nie podľa celkového súčtu — inak by tu navždy trónilo to
                         isté video spred rokov. --}}
                    <x-dashboard.panel title="Najsledovanejšie za 30 dní" flush>
<x-slot name="note">zhliadnutí v okne</x-slot>

                        @forelse ($topPosts as $index => $row)
                            <a href="{{ $postUrl($row) }}" class="ar-row">
                                <span class="ar-rank {{ $index === 0 ? 'ar-rank--first' : '' }}">{{ $index + 1 }}</span>
                                <span class="min-w-0">
                                    <span class="ar-row__title ar-clamp-2">{{ $row->title }}</span>
                                    <span class="ar-row__meta">{{ $num($row->count_view) }} celkovo</span>
                                </span>
                                <span class="ar-row__value">{{ $num($row->period_views) }}</span>
                            </a>
                        @empty
                            <x-dashboard.empty>Za posledných 30 dní zatiaľ nemáme namerané zhliadnutia.</x-dashboard.empty>
                        @endforelse
                    </x-dashboard.panel>

                    {{-- Kontrola, že import beží a že nové veci idú von. --}}
                    <x-dashboard.panel title="Posledné príspevky" flush>
<x-slot name="note">zhliadnutí celkovo</x-slot>

                        @forelse ($latestPosts as $row)
                            <a href="{{ $postUrl($row) }}" class="ar-row">
                                <span class="min-w-0">
                                    <span class="ar-row__title ar-clamp-2">{{ $row->title }}</span>
                                    <span class="ar-row__meta">
                                        {{ \Carbon\Carbon::parse($row->created_at)->diffForHumans() }}
                                        @if (! $row->published)
                                            <span class="ar-badge ar-badge--count ml-1">čaká v bufferi</span>
                                        @endif
                                        @if ($row->video_available !== null && ! $row->video_available)
                                            <span class="ar-badge ml-1 bg-amber-50 text-amber-700">video nedostupné</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="ar-row__value">{{ $num($row->count_view) }}</span>
                            </a>
                        @empty
                            <x-dashboard.empty>Kanál zatiaľ nemá žiadny príspevok.</x-dashboard.empty>
                        @endforelse

                        <x-slot name="footer">
                            <a href="{{ route('profile.canals.posts.index', $canal->id) }}" class="ar-link text-gray-500 hover:text-gray-900">
                                Všetky články kanála <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </x-slot>
                    </x-dashboard.panel>
                </div>

                {{-- ---- Bočný panel ------------------------------------- --}}

                <aside class="min-w-0 space-y-6 lg:col-span-4">

                    {{-- Fronta buffera. Zaujímavá len keď v nej niečo je —
                         prázdny panel by na nástenke len zaberal miesto. --}}
                    @if ($posts->waiting > 0)
                        <x-dashboard.panel title="Čaká na zverejnenie" flush>
<x-slot name="note">{{ $num($posts->waiting) }}</x-slot>

                            @foreach ($waitingPosts as $row)
                                <div class="ar-row">
                                    <span class="min-w-0">
                                        <span class="ar-row__title ar-clamp-2">{{ $row->title }}</span>
                                        <span class="ar-row__meta">v rade od {{ \Carbon\Carbon::parse($row->created_at)->diffForHumans() }}</span>
                                    </span>
                                </div>
                            @endforeach

                            <x-slot name="footer">
                                Buffer púšťa príspevky postupne počas dňa.
                            </x-slot>
                        </x-dashboard.panel>
                    @endif

                    <x-dashboard.panel title="Posledné komentáre" flush>
<x-slot name="note">{{ $num($comments->total) }} celkovo</x-slot>

                        @forelse ($latestComments as $row)
                            <a href="{{ url("/post/{$row->post_id}/{$row->post_slug}") }}" class="ar-row">
                                <span class="min-w-0">
                                    <span class="ar-row__title">{{ $shorten($row->body) }}</span>
                                    <span class="ar-row__meta">
                                        {{ $row->user_name ?: 'návštevník' }}
                                        <span class="mx-1 text-gray-300">·</span>
                                        {{ \Carbon\Carbon::parse($row->created_at)->diffForHumans() }}
                                    </span>
                                </span>
                            </a>
                        @empty
                            <x-dashboard.empty>Pod príspevkami kanála zatiaľ nikto nekomentoval.</x-dashboard.empty>
                        @endforelse
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Modlitby" flush>


                        <div class="ar-panel__body grid grid-cols-2 gap-4">
                            <div class="ar-stat">
                                <span class="ar-stat__value">{{ $num($prayers->open) }}</span>
                                <span class="ar-stat__label">otvorených</span>
                            </div>
                            <div class="ar-stat">
                                <span class="ar-stat__value">{{ $num($prayers->fulfilled) }}</span>
                                <span class="ar-stat__label">vypočutých</span>
                            </div>
                        </div>

                        <x-slot name="footer">
                            <a href="{{ route('profile.canals.prayers.index', $canal->id) }}" class="ar-link text-gray-500 hover:text-gray-900">
                                @if ($prayers->current > 0)
                                    {{ $num($prayers->current) }} {{ $plural($prayers->current, 'nová za 30 dní', 'nové za 30 dní', 'nových za 30 dní') }}
                                @else
                                    Spravovať modlitby
                                @endif
                                <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </x-slot>
                    </x-dashboard.panel>

                    {{-- Čísla, ktoré sa nemenia z týždňa na týždeň, ale správca
                         ich občas potrebuje — preto dole a v drobnom. --}}
                    <x-dashboard.panel title="Prehľad kanála" flush>


                        <div class="ar-panel__body space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Zverejnené články</span>
                                <span class="font-semibold tabular-nums">{{ $num($posts->published) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Semináre</span>
                                <span class="font-semibold tabular-nums">{{ $num($seminars->total) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Zhliadnutia celkovo</span>
                                <span class="font-semibold tabular-nums">{{ $num($posts->views_total) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">V koši</span>
                                <span class="font-semibold tabular-nums">{{ $num($posts->trashed) }}</span>
                            </div>
                        </div>
                    </x-dashboard.panel>
                </aside>
            </div>
        </x-dashboard.shell>
    @endif
@endsection
