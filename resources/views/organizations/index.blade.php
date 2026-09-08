@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    /*
     * Profil kanála. Hlavička nesie identitu a čísla kanála, stred jeho výpis
     * a bočný panel tri pohľady, ktoré vo výpise nie sú vidieť: najsledovanejšie
     * príspevky, posledné komentáre a mriežku archívu.
     */

    $months = ['január', 'február', 'marec', 'apríl', 'máj', 'jún',
               'júl', 'august', 'september', 'október', 'november', 'december'];

    // Slovenčina má pri počtoch tri tvary; v paneloch sa opakujú, tak nech
    // sú na jednom mieste.
    $plural = function ($count, $one, $few, $many) {
        if ($count === 1) return $one;
        return $count >= 2 && $count <= 4 ? $few : $many;
    };

    // Milióny zobrazení sa v úzkej dlaždici nezmestia; pod milión ostáva
    // presné číslo, nad ním stačí rádová hodnota.
    $compact = function ($number) {
        $number = (int) $number;
        return $number >= 1000000
            ? number_format($number / 1000000, 1, ',', ' ') . ' mil.'
            : number_format($number, 0, ',', ' ');
    };

    /*
     * Odkazy v hlavičke a v archíve menia vždy len jednu vec a zvyšok výberu
     * nechávajú tak — inak by prepnutie na "Najsledovanejšie" zahodilo zvolený
     * mesiac a hľadanie. Hodnota null parameter z adresy vyhodí.
     */
    $channelUrl = function (array $changes = []) use ($organization) {
        $keep  = request()->only(['rok', 'mesiac', 'search', 'recomended', 'mostVisited', 'first']);
        $query = array_filter(
            array_merge($keep, $changes),
            fn ($value) => $value !== null && $value !== ''
        );

        return route('organizations.show', ['organization' => $organization->id] + $query);
    };

    // Prepínače výpisu zodpovedajú filtrom v App\Filters\PostFilters. Prepnutie
    // jedného zhasne ostatné, preto ich zoznam nesie aj tie nezvolené.
    $sorts = [
        'first'       => ['label' => 'Od začiatku',      'icon' => 'fas fa-hourglass-start'],
        'recomended'  => ['label' => 'Odporúčané',       'icon' => 'far fa-thumbs-up'],
        'mostVisited' => ['label' => 'Najsledovanejšie', 'icon' => 'far fa-eye'],
    ];
    $activeSort = collect(array_keys($sorts))->first(fn ($key) => request()->filled($key));
    $reset      = array_fill_keys(array_keys($sorts), null);

    $search = trim((string) request('search'));

    // Popis výberu nad výpisom. Skladá sa z toho, čo je práve zapnuté.
    $selection = [];
    if ($month) $selection[] = $months[$month - 1] . ' ' . $year;
    elseif ($year) $selection[] = 'rok ' . $year;
    if ($search !== '') $selection[] = '„' . $search . '“';
    if ($activeSort) $selection[] = mb_strtolower($sorts[$activeSort]['label']);
@endphp

@php
    /*
     * Značky pre vyhľadávače (partials/meta). Kanonická adresa vychádza
     * z aktuálnej — archív aj hľadanie držia svoj výber v query — a strany
     * za prvou sú spojené odkazmi prev/next.
     *
     * Náhľad zdieľania berie obrázok z najnovšieho príspevku kanála: je
     * v pomere 16:9 a dosť veľký na to, aby ho Facebook prijal. Avatar
     * kanála je na to malý.
     */
    $orgUrl = route('organizations.show', [$organization->id]);

    $orgListUrl = fn ($page) => $page > 1
        ? request()->fullUrlWithQuery(['page' => $page])
        : request()->fullUrlWithoutQuery('page');

    $orgPage = $posts->currentPage();

    $orgImage = optional(optional($posts->first())->images->first())->originalImageUrl;

    $orgDescription = strip_tags((string) $organization->description)
        ?: 'Kázne, prenosy bohoslužieb a videá kanála ' . $organization->title . ' na Hlase Cirkvi.';

    $seo = [
        'title' => $orgPage > 1
            ? $organization->title . ' – strana ' . $orgPage
            : $organization->title,
        'description' => $orgDescription,
        'canonical' => $orgListUrl($orgPage),
        'prev' => $orgPage > 1 ? $orgListUrl($orgPage - 1) : null,
        'next' => $posts->hasMorePages() ? $orgListUrl($orgPage + 1) : null,
        'type' => 'profile',
        'image' => $orgImage ? url($orgImage) : null,
        'image_alt' => $organization->title,
        'jsonld' => [
            array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $organization->title,
                'description' => \App\Support\Seo::text($orgDescription, 300),
                'url' => $orgUrl,
                'logo' => $organization->avatar
                    ? url(Storage::url('organizations/' . $organization->id . '/' . $organization->avatar))
                    : null,
                'sameAs' => array_values(array_filter([$organization->url_www])),
            ]),
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                [$organization->title, $orgUrl],
            ]),
        ],
    ];
@endphp

@section('headerCSS')
    {{-- Rovnaké písmo ako na úvodnej stránke a na detaile príspevku. Layout ho
         nenačítava globálne — staršie sekcie ostávajú na Robote. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
@endsection

@section('content')

    {{-- Hlavička kanála --}}
    <header class="border-b border-[color:var(--ar-line)] bg-white">
        <div class="mx-auto max-w-6xl px-4">

            <div class="py-3 text-sm text-gray-500">
                <a href="{{ url('/') }}" class="hover:text-gray-900">Hlas Cirkvi</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="text-gray-700">{{ $organization->title }}</span>
            </div>

            <div class="border-t border-[color:var(--ar-line)] pt-6">

                {{-- Identita a odber. Vlastný Vue komponent, preto stojí
                     samostatne a čísla kanála idú až pod neho. --}}
                <organization-page-header :organization="{{ $organization }}"></organization-page-header>

                @if (! $organization->published)
                    <p class="mb-5 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        <i class="fas fa-exclamation-triangle mr-1.5"></i>
                        Kanál je zrušený — nové príspevky už nepribúdajú.
                    </p>
                @endif

                {{-- Čísla kanála. Archív bez nich vyzerá rovnako, či má sto
                     alebo šesťtisíc príspevkov. --}}
                @if ($summary && $summary->posts_count > 0)
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-4">
                        <div class="ar-stat">
                            <span class="ar-stat__value">{{ number_format((int) $summary->posts_count, 0, ',', ' ') }}</span>
                            <span class="ar-stat__label">{{ $plural((int) $summary->posts_count, 'príspevok', 'príspevky', 'príspevkov') }}</span>
                        </div>

                        <div class="ar-stat">
                            <span class="ar-stat__value">{{ $compact($summary->views_sum) }}</span>
                            <span class="ar-stat__label">zhliadnutí</span>
                        </div>

                        <div class="ar-stat">
                            <span class="ar-stat__value">{{ number_format($commentsCount, 0, ',', ' ') }}</span>
                            <span class="ar-stat__label">{{ $plural($commentsCount, 'komentár', 'komentáre', 'komentárov') }}</span>
                        </div>

                        <div class="ar-stat">
                            <span class="ar-stat__value">{{ \Carbon\Carbon::parse($summary->first_at)->year }}</span>
                            <span class="ar-stat__label">v archíve od</span>
                        </div>
                    </div>
                @endif

                {{-- Kontakty kanála, ak ich profil má. Prázdny riadok sa
                     nevykreslí, nech pod číslami nezostane hluchá medzera. --}}
                @php
                    $contacts = array_filter([
                        'obec'  => optional($organization->village)->fullname,
                        'web'   => $organization->url_www,
                        'mail'  => $organization->email,
                        'phone' => $organization->phone,
                    ]);
                @endphp

                @if ($contacts)
                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-500">
                        @isset($contacts['obec'])
                            <span><i class="fas fa-map-marker-alt mr-1.5 text-[color:var(--ar-accent)]"></i>{{ $contacts['obec'] }}</span>
                        @endisset

                        @isset($contacts['web'])
                            <a href="{{ $contacts['web'] }}" target="_blank" rel="noopener nofollow"
                               class="ar-link hover:text-gray-900">
                                <i class="fas fa-globe mr-1.5"></i>{{ preg_replace('~^https?://(www\.)?~', '', rtrim($contacts['web'], '/')) }}
                            </a>
                        @endisset

                        @isset($contacts['mail'])
                            <a href="mailto:{{ $contacts['mail'] }}" class="ar-link hover:text-gray-900">
                                <i class="far fa-envelope mr-1.5"></i>{{ $contacts['mail'] }}
                            </a>
                        @endisset

                        @isset($contacts['phone'])
                            <span><i class="fas fa-phone mr-1.5"></i>{{ $contacts['phone'] }}</span>
                        @endisset
                    </div>
                @endif

                {{-- Prepínač výpisu a hľadanie v kanáli --}}
                <div class="mt-6 flex flex-wrap items-center gap-2 pb-6">
                    <a href="{{ $channelUrl($reset) }}"
                       class="ar-tab {{ $activeSort ? '' : 'ar-tab--on' }}">
                        <i class="far fa-clock"></i> Najnovšie
                    </a>

                    @foreach ($sorts as $key => $sort)
                        <a href="{{ $channelUrl(array_merge($reset, [$key => 'true'])) }}"
                           class="ar-tab {{ $activeSort === $key ? 'ar-tab--on' : '' }}">
                            <i class="{{ $sort['icon'] }}"></i> {{ $sort['label'] }}
                        </a>
                    @endforeach

                    {{-- Hľadanie ostáva v kanáli, preto si so sebou nesie
                         zvolený mesiac aj poradie ako skryté polia. --}}
                    <form action="{{ route('organizations.show', [$organization->id]) }}" method="GET"
                          class="ar-search {{ $search !== '' ? 'ar-search--open' : '' }} ml-auto">
                        @foreach (array_filter(request()->only(['rok', 'mesiac', 'recomended', 'mostVisited', 'first'])) as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach

                        <label for="channel-search" class="sr-only">Hľadať v kanáli</label>
                        <input id="channel-search" type="search" name="search" value="{{ $search }}"
                               placeholder="Hľadať v kanáli…">
                        <button type="submit" title="Hľadať"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="grid gap-10 lg:grid-cols-12">

            {{-- Výpis kanála --}}
            {{-- Čerstvý kanál nemá čím naplniť bočný panel; výpis si vtedy
                 vezme celú šírku, nech vedľa neho neostane prázdny stĺpec. --}}
            @php $hasAside = $topPosts->isNotEmpty() || $comments->isNotEmpty() || $archive->isNotEmpty(); @endphp

            <div class="min-w-0 {{ $hasAside ? 'lg:col-span-8' : 'lg:col-span-12' }}">

                {{-- Čo je práve zvolené a ako to zrušiť. Bez tohto riadka nie je
                     po kliknutí do archívu zjavné, prečo výpis zrazu končí. --}}
                @if ($selection)
                    <div class="ar-rule mb-5">
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold">{{ number_format($posts->total(), 0, ',', ' ') }}</span>
                            {{ $plural($posts->total(), 'príspevok', 'príspevky', 'príspevkov') }}
                            — {{ implode(', ', $selection) }}
                        </p>
                        <a href="{{ route('organizations.show', [$organization->id]) }}"
                           class="shrink-0 text-xs text-gray-400 hover:text-[color:var(--ar-accent)]">
                            <i class="fas fa-times mr-1"></i> zrušiť výber
                        </a>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
                    @forelse ($posts as $post)
                        @include('posts.card-front')
                    @empty
                        <p class="col-span-full rounded-lg border border-dashed border-[color:var(--ar-line)] bg-white px-4 py-10 text-center text-sm text-gray-500">
                            Pre tento výber sme v kanáli nenašli žiadny príspevok.
                        </p>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $posts->onEachSide(1)->links() }}
                </div>
            </div>

            {{-- Bočný panel. Panely sú spolu vyššie než okno, takže `sticky` by
                 ten spodný pri posune nechal trvale pod okrajom — panel sa
                 preto posúva so stránkou. --}}
            {{-- min-w-0: riadky v komentároch sú `truncate`, teda nezalomiteľné.
                 Bez toho by ich celá dĺžka roztiahla stĺpec mriežky a na mobile
                 by stránka odchádzala doprava. --}}
            <aside class="ar-aside min-w-0 lg:col-span-4 {{ $hasAside ? '' : 'hidden' }}">
                <div class="space-y-4">

                    {{-- Naj príspevky. Najnovšie nesie výpis vedľa, panel preto
                         ukazuje to, čo v ňom nie je vidieť — najsledovanejšie
                         kusy archívu. --}}
                    <x-aside.top-posts :items="$topPosts" title="Naj príspevky" />

                    {{-- Posledné komentáre pod príspevkami kanála --}}
                    @if ($comments->isNotEmpty())
                        <section class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                            <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">
                                Posledné komentáre
                            </h2>
                            <ul class="space-y-3">
                                @foreach ($comments as $comment)
                                    <li class="border-b border-[color:var(--ar-line)] pb-3 last:border-0 last:pb-0">
                                        <a href="{{ route('post.show', [$comment->post_id, $comment->post_slug]) }}"
                                           title="{{ $comment->post_title }}" class="group block">
                                            <p class="ar-clamp-2 text-sm leading-snug text-gray-700">
                                                {{ $comment->body }}
                                            </p>
                                            <p class="mt-1 truncate text-xs text-gray-400">
                                                {{-- Meno z importu prichádza ako youtubová prezývka
                                                     so zavináčom; ten pred ňou nič nehovorí. --}}
                                                <span class="font-medium text-gray-500">{{ ltrim((string) $comment->user_name, '@') ?: 'Anonym' }}</span>
                                                · {{ \Carbon\Carbon::parse($comment->created_at)->locale('sk')->diffForHumans() }}
                                            </p>
                                            <p class="mt-0.5 truncate text-xs text-gray-400 transition-colors group-hover:text-[color:var(--ar-accent)]">
                                                <i class="far fa-comment mr-1"></i>{{ $comment->post_title }}
                                            </p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    {{-- Mriežka archívu: rok na riadok, mesiac na políčko.
                         Sýtosť ukazuje, kedy kanál vydával najviac, a políčko
                         je zároveň odkaz na daný mesiac. --}}
                    @if ($archive->isNotEmpty())
                        @php
                            // Riadky prídu z databázy len za mesiace, v ktorých
                            // niečo vyšlo; zvyšok mriežky sa doplní nulami.
                            $byYear = [];
                            foreach ($archive as $row) {
                                $byYear[(int) $row->rok][(int) $row->mesiac] = (int) $row->pocet;
                            }
                            krsort($byYear);

                            /*
                             * Sýtosť sa neurčuje z počtu, ale z poradia mesiaca
                             * medzi ostatnými. Kanály vydávajú vyrovnane —
                             * pri stupnici od nuly (či len od najslabšieho
                             * mesiaca) by mriežka bola jedna červená plocha
                             * s jedným svetlým políčkom za rozbehový mesiac.
                             * Poradie rozloží odtiene rovnomerne nech je
                             * rozdelenie akékoľvek.
                             */
                            $levels = array_unique(array_merge(...array_map('array_values', $byYear)));
                            sort($levels);
                            $rank  = array_flip($levels);
                            $steps = max(1, count($levels) - 1);
                        @endphp

                        <section class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                            <div class="mb-3 flex items-baseline justify-between">
                                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">
                                    Archív kanála
                                </h2>
                                @if ($year)
                                    <a href="{{ $channelUrl(['rok' => null, 'mesiac' => null]) }}"
                                       class="text-xs text-gray-400 hover:text-[color:var(--ar-accent)]">celý archív</a>
                                @endif
                            </div>

                            <div class="ar-archive">
                                <span></span>
                                @foreach ($months as $name)
                                    <span class="ar-archive__head">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
                                @endforeach

                                @foreach ($byYear as $rok => $mesiace)
                                    <a href="{{ $channelUrl(['rok' => $rok, 'mesiac' => null]) }}"
                                       class="ar-archive__year {{ $year === $rok && ! $month ? 'is-on' : '' }}"
                                       title="Celý rok {{ $rok }}">{{ $rok }}</a>

                                    @for ($m = 1; $m <= 12; $m++)
                                        @php
                                            $count = $mesiace[$m] ?? 0;
                                            $shade = $count ? 0.14 + 0.86 * ($rank[$count] / $steps) : 0;
                                        @endphp

                                        @if ($count)
                                            <a href="{{ $channelUrl(['rok' => $rok, 'mesiac' => $m]) }}"
                                               class="ar-month {{ $year === $rok && $month === $m ? 'is-on' : '' }}"
                                               style="background: rgba(var(--ar-accent-rgb), {{ round($shade, 2) }})"
                                               title="{{ $months[$m - 1] }} {{ $rok }} — {{ $count }} {{ $plural($count, 'príspevok', 'príspevky', 'príspevkov') }}"></a>
                                        @else
                                            <span class="ar-month ar-month--empty"
                                                  title="{{ $months[$m - 1] }} {{ $rok }} — bez príspevku"></span>
                                        @endif
                                    @endfor
                                @endforeach
                            </div>

                            <p class="mt-3 flex items-center justify-end gap-1.5 text-[.65rem] text-gray-400">
                                menej
                                @foreach ([0.14, 0.4, 0.65, 1] as $shade)
                                    <span class="inline-block h-2.5 w-2.5 rounded-sm"
                                          style="background: rgba(var(--ar-accent-rgb), {{ $shade }})"></span>
                                @endforeach
                                viac
                            </p>
                        </section>
                    @endif
                </div>
            </aside>
        </div>
    </div>
@endsection
