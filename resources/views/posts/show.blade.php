@extends('layouts.article')

@php
    /*
     * Prvý obrázok nesie článok ako úvodný, zvyšok ide do galérie pod text.
     * Pri videu je úvodný obrázok zbytočný — miesto nad titulkom patrí
     * prehrávaču.
     */
    $images   = $post->images;
    $lead     = $post->video_id ? null : $images->first();
    // Prvý obrázok je pri videu jeho náhľad a pri článku už visí nad textom;
    // do galérie ide až to, čo ostane.
    $gallery  = $images->slice(1);

    // Popisy z importu prichádzajú ako holý text so zalomeniami. Bez značiek
    // by sa v prehliadači zliali do jedného odseku.
    $plainBody = strip_tags($post->body) === $post->body;

    $plain    = \App\Support\Seo::text($post->body);
    // Nie každé importované video má popis. Náhrada používa iba údaje
    // zobrazené na stránke a objaví sa aj pod prehrávačom.
    $description = $plain;
    if ($post->video_id && $description === '') {
        $description = 'Video „' . \App\Support\Seo::text($post->title)
            . '“ z kanála ' . \App\Support\Seo::text($post->organization->title) . '.';
    }
    $words    = $plain === '' ? 0 : count(preg_split('/\s+/u', $plain));
    // 180 slov za minútu je bežný odhad pre pomalšie, čítané texty.
    $minutes  = max(1, (int) ceil($words / 180));

    $postUrl  = route('post.show', [$post->id, $post->slug]);
    $orgUrl   = route('organizations.show', [$post->organization_id]);

    /*
     * Značky pre vyhľadávače a náhľady odkazov skladá partials/meta z tohto
     * poľa; vykresľuje ich layout, nie táto šablóna.
     *
     * Štruktúrované dáta sú dve. Pri videu VideoObject — z neho má Google
     * náhľad s dĺžkou a stopou vo výsledkoch aj v záložke Videá; pri texte
     * Article. Drobčeky idú navyše, tie sa vo výsledku zobrazia namiesto
     * holej adresy.
     */
    // Náhľad zdieľania. Keď príspevok vlastný obrázok nemá, ale nesie video,
    // zoberie sa náhľad z YouTube — hqdefault existuje ku každému videu
    // a je dosť veľký na to, aby ho Facebook prijal (maxresdefault nie vždy).
    $ogImage = $images->first()
        ? url($images->first()->originalImageUrl)
        : ($post->video_id ? 'https://i.ytimg.com/vi/' . $post->video_id . '/hqdefault.jpg' : null);

    $schema = [
        '@context'      => 'https://schema.org',
        '@type'         => $post->video_id ? 'VideoObject' : 'Article',
        'name'          => $post->title,
        'headline'      => \Illuminate\Support\Str::limit($post->title, 110, ''),
        'description'   => \Illuminate\Support\Str::limit($description, 300),
        'url'           => $postUrl,
        'mainEntityOfPage' => $postUrl,
        'inLanguage'    => 'sk-SK',
        'datePublished' => optional($post->created_at)->toAtomString(),
        'dateModified'  => optional($post->updated_at)->toAtomString(),
        'author'        => [
            '@type' => 'Organization',
            'name'  => $post->organization->title,
            'url'   => $orgUrl,
        ],
        'publisher'     => \App\Support\Seo::publisher(),
    ];

    if ($ogImage) {
        $schema[$post->video_id ? 'thumbnailUrl' : 'image'] = $ogImage;
    }

    if ($post->video_id) {
        // uploadDate je pri VideoObject povinný a duration musí byť v ISO 8601
        // (PT12M3S) — presne v tvare, v akom hodnota leží v databáze. Cast
        // VideoDuration ju pre šablóny prepisuje na „12:03".
        $schema['uploadDate'] = optional($post->created_at)->toAtomString();
        $schema['embedUrl']   = 'https://www.youtube.com/embed/' . $post->video_id;
        // contentUrl patrí priamemu videosúboru; pri YouTube poznáme embedUrl.

        if ($duration = \App\Support\Seo::videoDuration($post->getRawOriginal('video_duration'))) {
            $schema['duration'] = $duration;
        }

        if ($post->count_view) {
            $schema['interactionStatistic'] = [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/WatchAction',
                'userInteractionCount' => (int) $post->count_view,
            ];
        }
    }

    $seo = [
        'title'       => $post->title,
        'description' => $description,
        'canonical'   => $postUrl,
        'type'        => 'article',
        'image'       => $ogImage,
        'image_alt'   => $post->title,
        'published'   => $post->created_at,
        'modified'    => $post->updated_at,
        'author'      => $post->organization->title,
        'section'     => 'Kázne a videá',
        // Facebook prehrá video priamo v príspevku, keď mu dáme adresu vloženého
        // prehrávača; bez nej vykreslí len obrázok s odkazom.
        'video'       => $post->video_id
            ? ['url' => 'https://www.youtube.com/embed/' . $post->video_id]
            : null,
        'jsonld'      => [
            $schema,
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                [$post->organization->title, $orgUrl],
                [$post->title, $postUrl],
            ]),
        ],
    ];
@endphp

@push('head')
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css">
@endpush

@section('content')

    <div class="ar-progress js-reading-progress"></div>

    {{-- Drobčeky --}}
    <div class="border-b border-[color:var(--ar-line)] bg-white">
        <div class="mx-auto max-w-6xl px-4 py-3 text-sm text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-900">Hlas Cirkvi</a>
            <span class="mx-2 text-gray-300">/</span>
            <a href="{{ $orgUrl }}" class="hover:text-gray-900">{{ $post->organization->title }}</a>
        </div>
    </div>

    {{-- Hlavička článku --}}
    <header class="border-b border-[color:var(--ar-line)] bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 md:py-12">
            {{-- Názov kanála nesú drobčeky nad hlavičkou a lišta kanála pod
                 ňou; tretíkrát nad titulkom už len opakoval to isté.

                 Titulok s údajmi drží šírku textu, akcie idú k pravému okraju
                 stránky — tam, kde sa v článku aj inde hľadá ovládanie. --}}
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-5">
                <div class="max-w-3xl">
                    <h1 class="ar-display text-3xl font-extrabold leading-tight md:text-[2.6rem]">
                        {{ $post->title }}
                    </h1>

                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-500">
                        <time datetime="{{ $post->created_at->toIso8601String() }}">
                            <i class="far fa-calendar mr-1.5 text-[color:var(--ar-accent)]"></i>
                            {{ $post->created_at->locale('sk')->isoFormat('D. MMMM YYYY') }}
                        </time>

                        @if ($post->video_id && $post->video_duration)
                            <span><i class="far fa-play-circle mr-1.5"></i>{{ $post->video_duration }}</span>
                        @elseif ($words > 0)
                            <span><i class="far fa-clock mr-1.5"></i>{{ $minutes }} min čítania</span>
                        @endif

                        <span><i class="far fa-eye mr-1.5"></i>{{ number_format($post->count_view, 0, ',', ' ') }}</span>
                    </div>
                </div>

                {{-- Akcie článku. Stáli pod textom, kde ich čitateľ našiel až
                     po dočítaní, a odporúčanie pod poslednou fotkou vyzeralo,
                     akoby patrilo ku galérii. Pri titulku sú tam, kde sa
                     o príspevku rozhoduje. --}}
                @if ($post->video_id || Gate::allows('update', $post))
                    <div class="flex items-center gap-2">
                        @if ($post->video_id)
                            @if (Session::get($post->slug) == $post->id)
                                <span class="ar-btn ar-btn--still">
                                    <i class="far fa-thumbs-up"></i> Už ste odporučili
                                </span>
                            @else
                                <favorite-post :post="{{ $post }}"></favorite-post>
                            @endif
                        @endif

                        @can('update', $post)
                            <article-dropdown :post="{{ $post }}" align="right" />
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- Médium: prehrávač alebo úvodná fotka --}}
        @if ($post->video_id)
            <div class="mb-8 overflow-hidden rounded-lg border border-[color:var(--ar-line)] bg-black">
                <div class="ar-player">
                    <div id="player">
                        <iframe
                            src="https://www.youtube.com/embed/{{ $post->video_id }}?origin={{ rawurlencode(request()->getSchemeAndHttpHost()) }}&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
                            allowfullscreen allowtransparency allow="autoplay"></iframe>
                    </div>
                </div>
            </div>
        @elseif ($lead)
            <figure class="mb-8">
                <a href="{{ url($lead->originalImageUrl) }}" target="_blank" rel="noopener"
                   data-lightbox="post" data-lightbox-fallback="{{ url($lead->thumbImageUrl) }}"
                   class="block cursor-zoom-in overflow-hidden rounded-lg border border-[color:var(--ar-line)] bg-white">
                    @if ($lead->variants)
                        {{-- Nové obrázky majú viac šírok aj WebP variant. Obrázok
                             nad článkom je nad ohybom, preto sa načíta rovno
                             a neprechádza cez lazysizes. --}}
                        <picture>
                            @if ($leadWebp = $lead->srcset('webp'))
                                <source type="image/webp" srcset="{{ $leadWebp }}"
                                        sizes="(min-width: 1024px) 720px, 100vw">
                            @endif
                            <img src="{{ url($lead->originalImageUrl) }}" srcset="{{ $lead->srcset('jpg') }}"
                                 sizes="(min-width: 1024px) 720px, 100vw"
                                 @if ($lead->width) width="{{ $lead->width }}" height="{{ $lead->height }}" @endif
                                 alt="{{ $post->title }}" class="w-full">
                        </picture>
                    @else
                        {{-- Staršie záznamy majú jediný súbor a originál občas
                             v úložisku chýba, náhľad býva vždy — bez zálohy by
                             nad článkom ostal prázdny rám s alt textom. --}}
                        <img src="{{ url($lead->originalImageUrl) }}" alt="{{ $post->title }}" class="w-full"
                             @if ($lead->width) width="{{ $lead->width }}" height="{{ $lead->height }}" @endif
                             onerror="this.onerror=null; this.src='{{ url($lead->thumbImageUrl) }}';">
                    @endif
                </a>
            </figure>
        @endif

        <div class="grid gap-10 lg:grid-cols-12">

            {{-- Článok --}}
            <article class="lg:col-span-8">

                {{-- Lišta kanála: avatar, názov a odber. Vlastný Vue komponent,
                     preto stojí na plnú šírku článku a nie v úzkom paneli. --}}
                <div class="mb-8 rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                    {{-- h1 na tejto stránke patrí titulku článku, kanál preto
                         dostane obyčajný riadok. --}}
                    <organization-page-header heading="div"
                                              :organization="{{ $post->organization }}"></organization-page-header>
                </div>

                @if ($plain !== '')
                    <div class="ar-prose max-w-none {{ $plainBody ? 'ar-prose--plain' : 'ar-prose--drop' }}">
                        {!! $post->body !!}
                    </div>
                @elseif ($post->video_id)
                    <p class="text-gray-500">{{ $description }}</p>
                @else
                    <p class="text-gray-500">Príspevok zatiaľ nemá text.</p>
                @endif

                {{-- Galéria: obrázky, ktoré sa nedostali nad titulok --}}
                @if ($gallery->isNotEmpty())
                    <div class="mt-10">
                        <h2 class="ar-rule ar-display mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Fotografie
                        </h2>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($gallery as $image)
                                <a href="{{ url($image->originalImageUrl) }}" target="_blank" rel="noopener"
                                   {{-- Na čiernej ploche sa ukazuje originál (miniatúra by sa
                                        roztiahnutím rozmazala), náhľad z mriežky je záloha. --}}
                                   data-lightbox="post" data-lightbox-fallback="{{ url($image->thumbImageUrl) }}"
                                   class="block cursor-zoom-in overflow-hidden rounded-md border border-[color:var(--ar-line)] bg-white">
                                    <picture class="block">
                                        @if ($galleryWebp = $image->srcset('webp'))
                                            <source type="image/webp" data-srcset="{{ $galleryWebp }}"
                                                    data-sizes="auto">
                                        @endif
                                        <img data-src="{{ url($image->thumbImageUrl) }}"
                                             @if ($gallerySrcset = $image->srcset('jpg')) data-srcset="{{ $gallerySrcset }}" @endif
                                             data-sizes="auto"
                                             alt="{{ $image->name ?? $post->title }}"
                                             class="lazyload h-32 w-full object-cover transition hover:scale-105">
                                    </picture>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Odporúčanie a správa článku sú v hlavičke pri titulku;
                     pod textom tak nasledujú rovno komentáre. --}}
                <div class="mt-10 border-t border-[color:var(--ar-line)] pt-8">
                    <comments-post :post="{{ $post }}"></comments-post>
                </div>
            </article>

            {{-- Bočný panel --}}
            <aside class="lg:col-span-4">
                <div class="space-y-4 lg:sticky lg:top-6">

                    {{-- Zdieľanie --}}
                    <section class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                        <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Zdieľať</h2>
                        <div class="flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($postUrl) }}"
                               target="_blank" rel="noopener" title="Zdieľať na Facebooku"
                               class="flex h-9 w-9 items-center justify-center rounded-md border border-[color:var(--ar-line)] text-gray-500 transition hover:border-blue-400 hover:text-blue-600">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . $postUrl) }}"
                               target="_blank" rel="noopener" title="Poslať cez WhatsApp"
                               class="flex h-9 w-9 items-center justify-center rounded-md border border-[color:var(--ar-line)] text-gray-500 transition hover:border-green-400 hover:text-green-600">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="mailto:?subject={{ rawurlencode($post->title) }}&body={{ rawurlencode($postUrl) }}"
                               title="Poslať e-mailom"
                               class="flex h-9 w-9 items-center justify-center rounded-md border border-[color:var(--ar-line)] text-gray-500 transition hover:border-red-300 hover:text-[color:var(--ar-accent)]">
                                <i class="far fa-envelope"></i>
                            </a>
                            <button type="button" data-url="{{ $postUrl }}"
                                    class="js-copy-link flex h-9 flex-1 items-center justify-center gap-2 rounded-md border border-[color:var(--ar-line)] text-sm text-gray-500 transition hover:border-red-300 hover:text-[color:var(--ar-accent)]">
                                <i class="far fa-copy"></i> Kopírovať odkaz
                            </button>
                        </div>
                    </section>

                    {{-- Podujatia už nedržíme v tejto databáze, žijú na portáli
                         event.hlascirkvi.sk. Panel preto neukazuje akcie práve
                         tohto kanála, len odkaz na celý výpis. --}}
                    <section class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                        <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">
                            Podujatia
                        </h2>
                        <p class="text-sm text-gray-500">
                            Pozrite si
                            <a href="{{ route('akcie.index') }}" class="text-[color:var(--ar-accent)] hover:underline">plánované podujatia</a>
                            z celého Slovenska.
                        </p>
                    </section>

                    {{-- Naj z kanála. Najnovšie príspevky nesie pás pod
                         článkom, panel preto ukazuje to, čo vo výpise nie je
                         vidieť: najsledovanejšie kusy archívu. --}}
                    <x-aside.top-posts :items="$topPosts" title="Naj z kanála" />

                    {{-- Z archívu. Dva pohľady dozadu, ktoré sa v bežnom výpise
                         nikdy neukážu — prvý príspevok kanála a to, čo v ňom
                         vyšlo pred rokom. array_filter vyhodí to, čo kanál
                         ešte nemá. --}}
                    @php $archive = array_filter(['Pred rokom' => $yearAgo, 'Ako to začalo' => $firstPost]); @endphp
                    @if ($archive)
                        <section class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                            <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">
                                Z archívu
                            </h2>
                            <ul class="space-y-3">
                                @foreach ($archive as $label => $item)
                                    <li>
                                        <a href="{{ route('post.show', [$item->id, $item->slug]) }}"
                                           title="{{ $item->title }}" class="group flex gap-3">
                                            <img data-src="{{ $item->thumbImage }}" data-sizes="auto" alt=""
                                                 class="lazyload h-12 w-16 shrink-0 rounded object-cover">
                                            <span class="min-w-0 flex-1">
                                                <span class="ar-kicker block text-[.6rem]">{{ $label }}</span>
                                                <span class="ar-clamp-2 mt-0.5 text-sm leading-snug transition-colors group-hover:text-[color:var(--ar-accent)]">
                                                    {{ $item->title }}
                                                </span>
                                                <time datetime="{{ $item->created_at->toIso8601String() }}"
                                                      class="mt-0.5 block text-xs text-gray-400">
                                                    {{ $item->created_at->locale('sk')->isoFormat('D. MMMM YYYY') }}
                                                </time>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>
            </aside>
        </div>

        {{-- Archív kanála. Bol to vodorovný pás: ovládal sa šípkami, na dotyku
             sa ťahal prstom a v jednom rade toho veľa nebolo vidieť. Mriežka
             ukáže celý riadok naraz a tlačidlo pod ňou pridá ďalší. --}}
        <section class="mt-14" data-archive
                 data-archive-url="{{ route('post.rail', $post) }}"
                 data-archive-next="{{ optional($rail->nextCursor())->encode() }}">

            <div class="ar-rule mb-5">
                <h2 class="ar-display text-lg font-bold">
                    Všetko od {{ $post->organization->title }}
                </h2>
                @if ($railTotal > 1)
                    <span class="shrink-0 text-xs text-gray-400">
                        {{ number_format($railTotal, 0, ',', ' ') }}
                        {{ $railTotal < 5 ? 'príspevky' : 'príspevkov' }}
                    </span>
                @endif
            </div>

            @if ($rail->isEmpty())
                <p class="text-sm text-gray-500">Kanál zatiaľ nemá ďalšie príspevky.</p>
            @else
                {{-- Dávka archívu je šesť príspevkov, preto šesť stĺpcov na
                     širokej obrazovke — jedno kliknutie pridá presne jeden
                     ďalší riadok. --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6"
                     data-archive-grid>
                    @include('posts._rail-items', ['items' => $rail])
                </div>

                {{-- Bez skriptu je to obyčajný odkaz na kanál, so skriptom
                     doťahuje ďalšiu dávku rovno pod mriežku. --}}
                <div class="mt-6 flex justify-center">
                    <a href="{{ $orgUrl }}" class="ar-btn ar-btn--quiet" data-archive-more>
                        <i class="fas fa-arrow-down"></i>
                        <span data-archive-label>Viac príspevkov</span>
                    </a>
                </div>
            @endif
        </section>

        <div class="mt-12 border-t border-[color:var(--ar-line)] pt-6">
            <a href="{{ $orgUrl }}" class="text-sm text-gray-500 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Späť na kanál {{ $post->organization->title }}
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($post->video_id)
        <script src="https://cdn.plyr.io/3.5.3/plyr.js"></script>
        <script>
            window.arReady(function () {
                // Vue must finish replacing #app before Plyr attaches its controls.
                if (typeof window.Plyr === 'function') {
                    new window.Plyr('#player');
                }
            });
        </script>
    @endif

    {{-- App ID ide z konfigurácie, aby sedelo s fb:app_id v meta značkách
         a s FB.init v partials/analyticstracking. --}}
    <script async defer crossorigin="anonymous"
            src="https://connect.facebook.net/sk_SK/sdk.js#xfbml=1&version=v5.0&appId={{ config('seo.facebook_app_id') }}"></script>


@endpush
