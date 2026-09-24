@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    /*
     * Značky pre vyhľadávače (partials/meta). Kanonická adresa nesie len
     * stranu — sledovacie parametre (fbclid, utm_*) do nej nepatria. Strany
     * za prvou ukazujú samy na seba a spája ich prev/next. Prepínače poradia
     * (najsledovanejšie, trendy…) sú len iné zoradenie toho istého obsahu,
     * preto noindex.
     */
    $listUrl = fn ($page) => \App\Support\Seo::listUrl([], $page);

    $listPage = $posts->currentPage();

    $seo = [
        'title' => $listPage > 1
            ? 'Kázne a videá kresťanských spoločenstiev – strana ' . $listPage
            : 'Kázne, videá a modlitby kresťanských spoločenstiev',
        'description' => 'Kázne, prenosy bohoslužieb a videá kresťanských spoločenstiev na Slovensku '
            . 'na jednom mieste. Nové príspevky každý deň, modlitebný múr aj denné zamyslenia.',
        'canonical' => $listUrl($listPage),
        'noindex' => \App\Support\Seo::hasQuery(['mostVisited', 'recomended', 'first', 'latestComments', 'trends', 'search']) ?: null,
        'prev' => $listPage > 1 ? $listUrl($listPage - 1) : null,
        'next' => $posts->hasMorePages() ? $listUrl($listPage + 1) : null,
        'jsonld' => [
            \App\Support\Seo::website(),
            \App\Support\Seo::publisher(),
        ],
    ];
@endphp

@section('headerCSS')
    {{-- Rovnaké písmo ako na detaile príspevku, nech na seba obe stránky
         nadväzujú. Layout ho nenačítava globálne — staršie sekcie ostávajú
         na Robote. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
@endsection

@section('content')

    @php
        // Prepínače výpisu zodpovedajú filtrom v App\Filters\PostFilters.
        $views = [
            'recomended'  => ['label' => 'Odporúčané',       'icon' => 'far fa-thumbs-up',
                              'perex' => 'Príspevky, ktoré odporučili naši čitatelia.'],
            'trends'      => ['label' => 'Trend',            'icon' => 'fas fa-sort-amount-up',
                              'perex' => 'Najsledovanejšie videá zverejnené za posledné dva týždne.'],
            'mostVisited' => ['label' => 'Najsledovanejšie', 'icon' => 'far fa-eye',
                              'perex' => 'Príspevky podľa celkového počtu zobrazení.'],
        ];

        $active = collect(array_keys($views))->first(fn ($key) => request()->filled($key));
        $search = trim((string) request('search'));

        if ($search !== '') {
            $heading = 'Výsledky hľadania';
            $perex   = 'Príspevky obsahujúce „' . $search . '“.';
        } elseif ($active) {
            $heading = $views[$active]['label'];
            $perex   = $views[$active]['perex'];
        } else {
            $heading = 'Príspevky kresťanskej komunity';
            $perex   = 'Kázne, modlitby a videá z kanálov kresťanských spoločenstiev.';
        }
    @endphp

    {{-- Hlavička výpisu --}}
    <header class="border-b border-[color:var(--ar-line)] bg-white">
        <div class="mx-auto max-w-6xl px-4 py-7 md:py-10">

            <div class="max-w-2xl">
                <h1 class="ar-display text-2xl font-extrabold leading-tight md:text-[2.1rem]">
                    {{ $heading }}
                </h1>

                <p class="mt-2 text-sm text-gray-500 md:text-base">{{ $perex }}</p>
            </div>

            {{-- Prepínač výpisu a hľadanie v jednom riadku. Na mobile sa
                 prepínače nezalamujú, ale posúvajú do strany, a hľadanie je len
                 ikona — po ťuknutí prekryje prepínače celou šírkou (.ar-viewbar
                 v partials/design-system). --}}
            <div class="ar-viewbar mt-6 {{ $search !== '' ? 'is-searching' : '' }}" data-viewbar>
                <nav class="ar-viewbar__tabs" aria-label="Zoradenie príspevkov">
                    <a href="{{ route('posts.index') }}"
                       class="ar-tab {{ $active || $search !== '' ? '' : 'ar-tab--on' }}">
                        <i class="far fa-clock"></i> Najnovšie
                    </a>

                    @foreach ($views as $key => $view)
                        <a href="{{ route('posts.index', [$key => 'true']) }}"
                           title="{{ $view['perex'] }}"
                           class="ar-tab {{ $active === $key ? 'ar-tab--on' : '' }}">
                            <i class="{{ $view['icon'] }}"></i> {{ $view['label'] }}
                        </a>
                    @endforeach
                </nav>

                <button type="button" class="ar-viewbar__icon ar-viewbar__open"
                        title="Hľadať" aria-label="Hľadať" data-viewbar-open>
                    <i class="fas fa-search"></i>
                </button>

                {{-- Hľadanie. Pole stojí vpravo zúžené a roztiahne sa až po
                     kliknutí; s vyplneným výrazom ostáva široké, nech je vidieť,
                     čo sa hľadalo. Prázdne pole filter preskočí, takže odoslanie
                     bez textu vráti bežný výpis. --}}
                <form action="{{ route('posts.index') }}" method="GET"
                      class="ar-search {{ $search !== '' ? 'ar-search--open' : '' }} ml-auto">
                    <label for="post-search" class="sr-only">Hľadať v príspevkoch</label>
                    <input id="post-search" type="search" name="search" value="{{ $search }}"
                           placeholder="Hľadať…">
                    <button type="submit" title="Hľadať"><i class="fas fa-search"></i></button>
                </form>

                <button type="button" class="ar-viewbar__icon ar-viewbar__close"
                        title="Zavrieť hľadanie" aria-label="Zavrieť hľadanie" data-viewbar-close>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="grid gap-10 lg:grid-cols-12">

            {{-- Výpis príspevkov --}}
            <div class="lg:col-span-8">
                {{-- Oznamy správcu webu. Stoja nad mriežkou, aby ich čitateľ
                     videl skôr než prvý príspevok. --}}
                <x-announcements placement="home" />

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
                    @forelse ($posts as $post)
                        @include('posts.card-front')
                    @empty
                        <p class="col-span-full rounded-lg border border-dashed border-[color:var(--ar-line)] bg-white px-4 py-10 text-center text-sm text-gray-500">
                            Pre tento výber sme nenašli žiadny príspevok.
                        </p>
                    @endforelse
                </div>

                {{-- Bez withQueryString by druhá strana zabudla zvolený
                     prepínač aj hľadaný výraz. --}}
                <div class="mt-8">
                    {{ $posts->onEachSide(1)->withQueryString()->links() }}
                </div>
            </div>

            {{-- Bočný panel --}}
            <aside class="ar-aside lg:col-span-4">
                <x-liturgical-readings />

                <x-announcements placement="sidebar" />

                <comments-card></comments-card>
                <prayers-card></prayers-card>

                <x-front-list-card type="personal" />
                <x-front-list-card type="organization" />
            </aside>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Mobilná lišta: ikona hľadania prepne riadok na pole, krížik späť na
        // prepínače. Aktívny prepínač sa navyše posunie do zorného poľa, keď
        // stojí mimo viditeľnej časti riadka. Vue pri štarte prekreslí #app,
        // preto delegované udalosti a posun až po načítaní stránky.
        document.addEventListener('click', function (e) {
            var open = e.target.closest('[data-viewbar-open]');
            var close = e.target.closest('[data-viewbar-close]');
            if (!open && !close) return;

            var bar = e.target.closest('[data-viewbar]');
            bar.classList.toggle('is-searching', !!open);
            if (open) bar.querySelector('input[type="search"]').focus();
        });

        window.addEventListener('load', function () {
            document.querySelectorAll('.ar-viewbar__tabs').forEach(function (tabs) {
                var on = tabs.querySelector('.ar-tab--on');
                if (!on) return;
                var overflow = on.getBoundingClientRect().right - tabs.getBoundingClientRect().right;
                if (overflow > 0) tabs.scrollLeft = overflow + 32;
            });
        });
    </script>
@endsection
