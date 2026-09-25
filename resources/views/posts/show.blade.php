@extends('layouts.article')

@php
    /*
     * Prvý obrázok nesie článok ako úvodný, zvyšok ide do galérie pod text.
     * Pri videu je úvodný obrázok zbytočný — miesto nad titulkom patrí
     * prehrávaču.
     */
    $videoUnavailable = $post->video_id && $post->video_available === false;
    $playableVideo = $post->video_id && ! $videoUnavailable;
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
            . '“ z kanála ' . \App\Support\Seo::text($post->canal->title) . '.';
    }
    $words    = $plain === '' ? 0 : count(preg_split('/\s+/u', $plain));
    // 180 slov za minútu je bežný odhad pre pomalšie, čítané texty.
    $minutes  = max(1, (int) ceil($words / 180));

    $postUrl  = route('post.show', [$post->id, $post->slug]);
    $canalUrl   = route('organizations.show', [$post->canal_id]);

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
        '@type'         => $playableVideo ? 'VideoObject' : 'Article',
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
            'name'  => $post->canal->title,
            'url'   => $canalUrl,
        ],
        'publisher'     => \App\Support\Seo::publisher(),
    ];

    if ($ogImage) {
        $schema[$playableVideo ? 'thumbnailUrl' : 'image'] = $ogImage;
    }

    if ($playableVideo) {
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
        'author'      => $post->canal->title,
        'section'     => 'Kázne a videá',
        // Facebook prehrá video priamo v príspevku, keď mu dáme adresu vloženého
        // prehrávača; bez nej vykreslí len obrázok s odkazom.
        'video'       => $playableVideo
            ? ['url' => 'https://www.youtube.com/embed/' . $post->video_id]
            : null,
        'jsonld'      => [
            $schema,
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                [$post->canal->title, $canalUrl],
                [$post->title, $postUrl],
            ]),
        ],
    ];
@endphp

@section('content')

    <div class="ar-progress js-reading-progress"></div>

    {{-- Drobčeky --}}
    <div class="border-b border-[color:var(--ar-line)] bg-white">
        <div class="mx-auto max-w-6xl px-4 py-3 text-sm text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-900">Hlas Cirkvi</a>
            <span class="mx-2 text-gray-300">/</span>
            <a href="{{ $canalUrl }}" class="hover:text-gray-900">{{ $post->canal->title }}</a>
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

                        @if ($post->video_id && $post->youtube_published_at
                            && ! $post->youtube_published_at->isSameDay($post->created_at))
                            <span title="Zverejnené na YouTube">
                                <i class="fab fa-youtube mr-1.5"></i>{{ $post->youtube_published_at->locale('sk')->isoFormat('D. MMMM YYYY') }}
                            </span>
                        @endif

                        @if ($post->video_id && $post->video_duration && $post->video_duration !== '0:00')
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
                {{-- Vue komponenty dostanú len polia, ktoré čítajú. Celý
                     $post by do HTML zapísal aj text, obrázky a kanál — pri
                     troch komponentoch trikrát. --}}
                <div class="flex flex-wrap items-center gap-2">
                    @if ($post->video_id)
                        @if (Session::get($post->slug) == $post->id)
                            <span class="ar-btn ar-btn--still">
                                <i class="far fa-thumbs-up"></i> Už ste odporučili
                            </span>
                        @else
                            <favorite-post :post="{{ json_encode($post->only(['id', 'favoritesCount', 'isFavorited'])) }}"></favorite-post>
                        @endif
                    @endif

                    <save-post :post-id="{{ $post->id }}" :initial-saved="{{ json_encode($isSaved) }}"></save-post>

                    {{-- Systémové zdieľanie. Panel so zdieľaním je na mobile
                         až pod článkom, preto tlačidlo aj tu; skript ho odkryje
                         len tam, kde Web Share API existuje. --}}
                    <button type="button" data-url="{{ $postUrl }}" data-title="{{ $post->title }}"
                            class="js-native-share ar-btn ar-btn--quiet !hidden lg:!hidden"
                            title="Zdieľať" aria-label="Zdieľať">
                        <i class="fas fa-share-alt"></i>
                    </button>

                    @can('update', $post)
                        <article-dropdown :post="{{ json_encode($post->only(['id'])) }}" align="right" />
                    @endcan
                </div>
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- Médium: prehrávač alebo úvodná fotka --}}
        @if ($videoUnavailable)
            <section class="mb-8 rounded-lg border border-[color:var(--ar-line)] p-6" aria-labelledby="video-unavailable-title">
                <h2 id="video-unavailable-title" class="text-xl font-semibold">Video je momentálne nedostupné</h2>
                <p class="mt-2">Toto video sa momentálne nedá prehrať na našom webe. Názov, popis a diskusia zostávajú dostupné.</p>
                <a href="{{ $canalUrl }}" class="ar-btn mt-4">Pozrieť ďalšie videá z tohto kanála</a>
            </section>
        @elseif ($playableVideo)
            <div class="mb-8 overflow-hidden rounded-lg border border-[color:var(--ar-line)] bg-black">
                {{-- Náhľad s tlačidlom namiesto iframe: prehrávač YouTube pri
                     načítaní stiahne okolo megabajtu skriptov aj návštevníkovi,
                     ktorý video nespustí. Iframe vloží až klik (skript dole). --}}
                @php $poster = $images->first(); @endphp
                <div class="ar-player">
                    <button type="button" class="ar-lite" data-yt-lite="{{ $post->video_id }}"
                            aria-label="Prehrať video: {{ $post->title }}">
                        @if ($poster && $poster->variants)
                            <picture>
                                @if ($posterWebp = $poster->srcset('webp'))
                                    <source type="image/webp" srcset="{{ $posterWebp }}"
                                            sizes="(min-width: 1152px) 1120px, 100vw">
                                @endif
                                <img src="{{ url($poster->originalImageUrl) }}" srcset="{{ $poster->srcset('jpg') }}"
                                     sizes="(min-width: 1152px) 1120px, 100vw" alt="" fetchpriority="high"
                                     @if ($poster->width) width="{{ $poster->width }}" height="{{ $poster->height }}" @endif>
                            </picture>
                        @else
                            {{-- Staršie náhľady majú jediný malý súbor; hqdefault
                                 existuje ku každému videu. --}}
                            <img src="https://i.ytimg.com/vi/{{ $post->video_id }}/hqdefault.jpg" alt=""
                                 width="480" height="360" fetchpriority="high">
                        @endif
                        <span class="ar-lite__play" aria-hidden="true">
                            <svg viewBox="0 0 68 48"><path d="M66.5 7.7a8.5 8.5 0 0 0-6-6C55.3.3 34 .3 34 .3s-21.3 0-26.5 1.4a8.5 8.5 0 0 0-6 6C.1 13 .1 24 .1 24s0 11 1.4 16.3a8.5 8.5 0 0 0 6 6C12.7 47.7 34 47.7 34 47.7s21.3 0 26.5-1.4a8.5 8.5 0 0 0 6-6C67.9 35 67.9 24 67.9 24s0-11-1.4-16.3z" fill="#f00"/><path d="M45 24 27 14v20z" fill="#fff"/></svg>
                        </span>
                    </button>
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

        {{-- Séria: poloha v seminári a posun na susedné diely hneď pod
             médiom, kde sa po dopozeraní hľadá „čo ďalej". --}}
        @if ($series)
            <nav aria-label="Diely série"
                 class="mb-8 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-[color:var(--ar-line)] bg-white px-4 py-3">
                <div class="min-w-0 text-sm">
                    <span class="ar-kicker block text-[.6rem]">Séria · časť {{ $series['index'] + 1 }} z {{ $series['parts']->count() }}</span>
                    <a href="{{ route('seminars.show', $series['seminar']) }}"
                       class="ar-display font-semibold hover:text-[color:var(--ar-accent)]">
                        {{ $series['seminar']->title }}
                    </a>
                </div>

                <div class="flex shrink-0 gap-2">
                    @if ($series['previous'])
                        <a href="{{ route('post.show', [$series['previous']->id, $series['previous']->slug]) }}"
                           rel="prev" title="{{ $series['previous']->title }}" class="ar-btn ar-btn--quiet">
                            <i class="fas fa-arrow-left"></i> Predchádzajúca
                        </a>
                    @endif
                    @if ($series['next'])
                        <a href="{{ route('post.show', [$series['next']->id, $series['next']->slug]) }}"
                           rel="next" title="{{ $series['next']->title }}" class="ar-btn ar-btn--accent">
                            Ďalšia <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </nav>
        @endif

        <div class="grid gap-10 lg:grid-cols-12">

            {{-- Článok --}}
            <article class="lg:col-span-8" data-view-url="{{ route('post.view', $post) }}">

                {{-- Lišta kanála: avatar, názov a odber. Vlastný Vue komponent,
                     preto stojí na plnú šírku článku a nie v úzkom paneli. --}}
                <div class="mb-8 rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                    {{-- h1 na tejto stránke patrí titulku článku, kanál preto
                         dostane obyčajný riadok. --}}
                    <canal-page-header heading="div"
                                       :canal="{{ json_encode($post->canal->only(['id', 'title', 'description', 'avatar', 'initialName', 'isFavorited'])) }}"></canal-page-header>
                </div>

                {{-- Automatické zhrnutie dlhého popisu (príkaz posts:summarize).
                     Stojí nad textom, nie namiesto neho. --}}
                @if ($post->summary && $plain !== '')
                    <aside class="mb-8 rounded-lg border border-[color:var(--ar-line)] bg-[color:var(--ar-accent-soft)] p-4 md:p-5">
                        {{-- Body („• …“) = zhrnutie; súvislý text = rozsah „Text na A4“. --}}
                        <h2 class="ar-kicker mb-2 text-[.65rem]">{{ str_starts_with(ltrim($post->summary), '•') ? 'V skratke' : 'Z obsahu' }}</h2>
                        <div class="text-[.95rem] leading-relaxed text-[color:var(--ar-ink)]">
                            {!! nl2br(e($post->summary)) !!}
                        </div>
                        <p class="mt-3 text-xs text-gray-500">
                            <i class="fas fa-magic mr-1"></i> Zhrnutie vytvorené automaticky z videa alebo popisu, môže obsahovať nepresnosti.
                        </p>
                    </aside>
                @endif

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
                <div id="komentare" class="mt-10 scroll-mt-20 border-t border-[color:var(--ar-line)] pt-8">
                    <comments-post :post="{{ json_encode($post->only(['id'])) }}"></comments-post>
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

                        {{-- Systémová ponuka zdieľania (Messenger, Signal…).
                             Skript ho odkryje len tam, kde ju prehliadač má. --}}
                        <button type="button" data-url="{{ $postUrl }}" data-title="{{ $post->title }}"
                                class="js-native-share mt-2 hidden h-9 w-full flex items-center justify-center gap-2 rounded-md border border-[color:var(--ar-line)] text-sm text-gray-500 transition hover:border-red-300 hover:text-[color:var(--ar-accent)]">
                            <i class="fas fa-share-alt"></i> Zdieľať cez…
                        </button>
                    </section>

                    {{-- Diely série. Pri dlhom seminári len okolie aktuálneho
                         dielu, celý zoznam je na stránke seminára. --}}
                    @if ($series)
                        @php
                            $partsTotal = $series['parts']->count();
                            $from = max(0, min($series['index'] - 3, $partsTotal - 7));
                            $visibleParts = $series['parts']->slice($from, 7);
                        @endphp
                        <section class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                            <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">
                                Diely série
                            </h2>
                            <ol class="space-y-1">
                                @foreach ($visibleParts as $number => $part)
                                    @php $current = $part->id === $post->id; @endphp
                                    <li>
                                        <a href="{{ route('post.show', [$part->id, $part->slug]) }}"
                                           @if ($current) aria-current="page" @endif
                                           class="flex gap-2 rounded-md px-2 py-1.5 text-sm leading-snug transition-colors {{ $current ? 'bg-[color:var(--ar-accent-soft)] font-semibold text-[color:var(--ar-accent)]' : 'hover:bg-gray-50 hover:text-[color:var(--ar-accent)]' }}">
                                            <span class="w-5 shrink-0 text-right tabular-nums text-gray-400">{{ $number + 1 }}.</span>
                                            <span class="ar-clamp-2 min-w-0 flex-1">{{ $part->title }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                            @if ($partsTotal > $visibleParts->count())
                                <a href="{{ route('seminars.show', $series['seminar']) }}"
                                   class="mt-3 block text-sm text-[color:var(--ar-accent)] hover:underline">
                                    Celá séria ({{ $partsTotal }} dielov)
                                </a>
                            @endif
                        </section>
                    @endif

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
                    Všetko od {{ $post->canal->title }}
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
                    <a href="{{ $canalUrl }}" class="ar-btn ar-btn--quiet" data-archive-more>
                        <i class="fas fa-arrow-down"></i>
                        <span data-archive-label>Viac príspevkov</span>
                    </a>
                </div>
            @endif
        </section>

        <div class="mt-12 border-t border-[color:var(--ar-line)] pt-6">
            <a href="{{ $canalUrl }}" class="text-sm text-gray-500 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Späť na kanál {{ $post->canal->title }}
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($post->video_id)
        <script>
            // Delegované na document: Vue pri mountnutí prekreslí celý #app
            // a poslucháč priamo na tlačidle by zahodil.
            // Delegované na document: Vue pri mountnutí prekreslí celý #app
            // a poslucháč priamo na tlačidle by zahodil.
            document.addEventListener('click', function (event) {
                var button = event.target.closest('[data-yt-lite]');

                if (!button) {
                    return;
                }

                var iframe = document.createElement('iframe');
                iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(button.dataset.ytLite)
                    + '?autoplay=1&rel=0&modestbranding=1&playsinline=1&iv_load_policy=3';
                iframe.title = button.getAttribute('aria-label');
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
                iframe.allowFullscreen = true;

                button.replaceWith(iframe);
                iframe.focus();
            });
        </script>
    @endif

    {{-- Druhý Facebook SDK tu už nie je: stránka nemá žiadny fb-* prvok
         a zdieľanie ide cez obyčajný odkaz sharer.php. --}}
@endpush
