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
            'recomended'  => ['label' => 'Odporúčané',       'icon' => 'ph ph-thumbs-up',
                              'perex' => 'Príspevky, ktoré odporučili naši čitatelia.'],
            'trends'      => ['label' => 'Trend',            'icon' => 'ph ph-sort-ascending',
                              'perex' => 'Najsledovanejšie videá zverejnené za posledné dva týždne.'],
            'mostVisited' => ['label' => 'Najsledovanejšie', 'icon' => 'ph ph-eye',
                              'perex' => 'Príspevky podľa celkového počtu zobrazení.'],
        ];

        $active = collect(array_keys($views))->first(fn ($key) => request()->filled($key));
        $search = trim((string) request('search'));
        $searchQuery = $search !== '' ? ['search' => $search] : [];

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
                    <a href="{{ route('posts.index', $searchQuery) }}"
                       @if (!$active) aria-current="page" @endif
                       class="ar-tab {{ $active || $search !== '' ? '' : 'ar-tab--on' }}">
                        <i class="ph ph-clock"></i> Najnovšie
                    </a>

                    @foreach ($views as $key => $view)
                        <a href="{{ route('posts.index', array_merge($searchQuery, [$key => 'true'])) }}"
                           @if ($active === $key) aria-current="page" @endif
                           title="{{ $view['perex'] }}"
                           class="ar-tab {{ $active === $key ? 'ar-tab--on' : '' }}">
                            <i class="{{ $view['icon'] }}"></i> {{ $view['label'] }}
                        </a>
                    @endforeach
                </nav>

                <button type="button" class="ar-viewbar__icon ar-viewbar__open"
                        title="Hľadať" aria-label="Hľadať" data-viewbar-open>
                    <i class="ph ph-magnifying-glass"></i>
                </button>

                {{-- Hľadanie. Pole stojí vpravo zúžené a roztiahne sa až po
                     kliknutí; s vyplneným výrazom ostáva široké, nech je vidieť,
                     čo sa hľadalo. Prázdne pole filter preskočí, takže odoslanie
                     bez textu vráti bežný výpis. --}}
                <form action="{{ route('posts.index') }}" method="GET"
                      class="ar-search {{ $search !== '' ? 'ar-search--open' : '' }} ml-auto">
                    @if ($active)
                        <input type="hidden" name="{{ $active }}" value="true">
                    @endif
                    <label for="post-search" class="sr-only">Hľadať v príspevkoch</label>
                    <input id="post-search" type="search" name="search" value="{{ $search }}"
                           placeholder="Hľadať…">
                    <button type="submit" title="Hľadať" aria-label="Hľadať v príspevkoch"><i class="ph ph-magnifying-glass" aria-hidden="true"></i></button>
                </form>

                <button type="button" class="ar-viewbar__icon ar-viewbar__close"
                        title="Zavrieť hľadanie" aria-label="Zavrieť hľadanie" data-viewbar-close>
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        {{-- Liturgia dňa stojí v kóde pred výpisom, aby ju mobil (jeden stĺpec)
             ukázal hneď pod prepínačmi — za 30 kartami by ju nikto nenašiel.
             Na desktope ju mriežka postaví do pravého stĺpca nad zvyšok
             panela; druhý riadok (1fr) pohltí výšku výpisu, takže medzi
             liturgiou a panelom nevznikne medzera. --}}
        <div class="grid gap-y-8 lg:grid-cols-12 lg:grid-rows-[auto_1fr] lg:gap-x-10 lg:gap-y-0">

            <aside class="ar-aside min-w-0 lg:col-span-4 lg:col-start-9 lg:row-start-1 lg:mb-4" aria-label="Liturgia dňa">
                <x-liturgical-readings />
            </aside>

            {{-- Výpis príspevkov --}}
            <div class="min-w-0 lg:col-span-8 lg:col-start-1 lg:row-span-2 lg:row-start-1">
                {{-- Oznamy správcu webu. Stoja nad mriežkou, aby ich čitateľ
                     videl skôr než prvý príspevok. --}}
                <x-announcements placement="home" />

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-3" data-post-grid>
                    @forelse ($posts as $post)
                        @include('posts.card-front')
                    @empty
                        <div class="col-span-full rounded-lg border border-dashed border-[color:var(--ar-line)] bg-white px-4 py-10 text-center text-sm text-gray-500">
                            <p>Pre tento výber sme nenašli žiadny príspevok.</p>
                            @if ($search !== '' || $active)
                                <p class="mt-2">Skúste iný výraz alebo zrušte vybraný filter.</p>
                                <a href="{{ route('posts.index') }}" class="ar-tab mt-4">Zobraziť najnovšie príspevky</a>
                            @endif
                        </div>
                    @endforelse
                </div>

                {{-- „Načítať ďalšie" pripojí ďalšiu stranu pod mriežku. Číselné
                     stránkovanie ostáva pod ním pre vyhľadávače, prehliadače bez
                     JavaScriptu a skok na staršie príspevky. Bez withQueryString
                     by druhá strana zabudla zvolený prepínač aj hľadaný výraz. --}}
                <div data-post-more>
                    @if ($posts->hasMorePages())
                        <div class="mt-8 text-center">
                            <a href="{{ $posts->withQueryString()->nextPageUrl() }}"
                               class="ar-tab ar-more" data-post-more-link>
                                Načítať ďalšie príspevky <i class="ph ph-caret-down" aria-hidden="true"></i>
                            </a>
                        </div>
                    @endif

                    <div class="mt-6">
                        {{ $posts->onEachSide(1)->withQueryString()->links() }}
                    </div>
                </div>
            </div>

            {{-- Bočný panel --}}
            <aside class="ar-aside min-w-0 lg:col-span-4 lg:col-start-9 lg:row-start-2" aria-label="Bočný panel">
                <x-upcoming-events />

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
    <script nonce="{{ csp_nonce() }}">
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

        // „Načítať ďalšie": stiahne ďalšiu stranu, jej karty pripojí do mriežky
        // a blok s tlačidlom a stránkovaním nahradí tým z novej strany. Pri
        // chybe prehliadač normálne prejde na odkaz. Fokus skočí na prvú novú
        // kartu, aby klávesnica nezačínala znova od tlačidla. Obrázky si
        // lazysizes nájde sám (sleduje pribudnuté .lazyload).
        document.addEventListener('click', function (e) {
            var link = e.target.closest('[data-post-more-link]');
            if (!link || link.getAttribute('aria-busy') === 'true') return;
            e.preventDefault();

            link.setAttribute('aria-busy', 'true');
            link.classList.add('opacity-60');

            fetch(link.href, { headers: { 'Accept': 'text/html' } })
                .then(function (r) { if (!r.ok) throw r; return r.text(); })
                .then(function (html) {
                    var page = new DOMParser().parseFromString(html, 'text/html');
                    var grid = document.querySelector('[data-post-grid]');
                    var cards = page.querySelectorAll('[data-post-grid] > article');
                    var first = null;

                    cards.forEach(function (card) {
                        var node = document.importNode(card, true);
                        grid.appendChild(node);
                        first = first || node;
                    });

                    document.querySelector('[data-post-more]')
                        .replaceWith(document.importNode(page.querySelector('[data-post-more]'), true));

                    var title = first && first.querySelector('h2 a');
                    if (title) title.focus({ preventScroll: true });
                })
                .catch(function () { window.location.href = link.href; });
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
