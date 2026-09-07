@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    /*
     * Značky pre vyhľadávače (partials/meta). Výpis nesie prepínače aj
     * stránkovanie v adrese, preto sa kanonická adresa skladá z tej
     * aktuálnej — každý pohľad tak ukazuje sám na seba a strany za prvou
     * sú spojené odkazmi prev/next namiesto toho, aby si konkurovali.
     */
    $listUrl = fn ($page) => $page > 1
        ? request()->fullUrlWithQuery(['page' => $page])
        : request()->fullUrlWithoutQuery('page');

    $listPage = $posts->currentPage();

    $seo = [
        'title' => $listPage > 1
            ? 'Kázne a videá kresťanských spoločenstiev – strana ' . $listPage
            : 'Kázne, videá a modlitby kresťanských spoločenstiev',
        'description' => 'Kázne, prenosy bohoslužieb a videá kresťanských spoločenstiev na Slovensku '
            . 'na jednom mieste. Nové príspevky každý deň, modlitebný múr aj denné zamyslenia.',
        'canonical' => $listUrl($listPage),
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
                              'perex' => 'Najsledovanejšie videá za posledné dva týždne.'],
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

            {{-- Prepínač výpisu a hľadanie v jednom riadku. Prepínač oproti
                 pôvodným ikonám drží popisky, takže je čitateľný aj na mobile,
                 kde bol predtým skrytý. --}}
            <div class="mt-6 flex flex-wrap items-center gap-2">
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
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="grid gap-10 lg:grid-cols-12">

            {{-- Výpis príspevkov --}}
            <div class="lg:col-span-8">
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
                <comments-card></comments-card>
                <prayers-card></prayers-card>

                @include('verses.daily-modul')
                @include('organizations.list-users')
            </aside>
        </div>
    </div>
@endsection
