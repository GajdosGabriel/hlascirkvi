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

    $plain    = trim(preg_replace('/\s+/u', ' ', strip_tags($post->body)));
    $words    = $plain === '' ? 0 : count(preg_split('/\s+/u', $plain));
    // 180 slov za minútu je bežný odhad pre pomalšie, čítané texty.
    $minutes  = max(1, (int) ceil($words / 180));

    $postUrl  = route('post.show', [$post->id, $post->slug]);
    $orgUrl   = route('organizations.show', [$post->organization_id]);
@endphp

@section('title')
    <title>{{ $post->title }} | Hlas Cirkvi</title>
@endsection

@section('meta')
    <meta name="description" content="{{ Str::limit($plain, 160) }}">
    <link rel="canonical" href="{{ $postUrl }}">

    <meta property="fb:app_id" content="241173683337522">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $postUrl }}">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ Str::limit($plain, 200) }}">
    @if ($images->first())
        <meta property="og:image" content="{{ url($images->first()->originalImageUrl) }}">
        <meta property="og:image:alt" content="{{ $post->title }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
@endsection

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
                 ňou; tretíkrát nad titulkom už len opakoval to isté. --}}
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
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- Médium: prehrávač alebo úvodná fotka --}}
        @if ($post->video_id)
            <div class="mb-8 overflow-hidden rounded-lg border border-[color:var(--ar-line)] bg-black">
                <div class="ar-player">
                    <div id="player">
                        <iframe
                            src="https://www.youtube.com/embed/{{ $post->video_id }}?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
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
                                 alt="{{ $post->title }}" class="w-full">
                        </picture>
                    @else
                        {{-- Staršie záznamy majú jediný súbor a originál občas
                             v úložisku chýba, náhľad býva vždy — bez zálohy by
                             nad článkom ostal prázdny rám s alt textom. --}}
                        <img src="{{ url($lead->originalImageUrl) }}" alt="{{ $post->title }}" class="w-full"
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
                <div class="mb-8 rounded-lg border border-[color:var(--ar-line)] bg-white px-4 pt-4">
                    <organization-page-header :organization="{{ $post->organization }}"></organization-page-header>
                </div>

                @if (trim(strip_tags($post->body)) !== '')
                    <div class="ar-prose max-w-none {{ $plainBody ? 'ar-prose--plain' : 'ar-prose--drop' }}">
                        {!! $post->body !!}
                    </div>
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

                {{-- Akcie autora a odporúčanie. Prázdny riadok by pod textom
                     nechal osamotenú linku, preto sa vykreslí len keď má čo
                     niesť. --}}
                @if ($post->video_id || Gate::allows('update', $post))
                    <div class="mt-10 flex flex-wrap items-center gap-3 border-t border-[color:var(--ar-line)] pt-5">
                        @if ($post->video_id)
                            @if (Session::get($post->slug) == $post->id)
                                <span class="text-sm text-gray-400">Toto video ste už odporučili.</span>
                            @else
                                {{-- Komponent má na koreni `grow`, takže by v pružnom
                                     riadku zabral celú šírku; obal ho stiahne na obsah. --}}
                                <div class="inline-flex">
                                    <favorite-post :post="{{ $post }}"></favorite-post>
                                </div>
                            @endif
                        @endif

                        @can('update', $post)
                            <article-dropdown :post="{{ $post }}" />
                        @endcan
                    </div>
                @endif

                @auth
                    @include('bigthink._form')
                @endauth

                <div class="mt-10">
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
                    @if ($topPosts->isNotEmpty())
                        <section class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
                            <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">
                                Naj z kanála
                            </h2>
                            <ol class="space-y-3">
                                @foreach ($topPosts as $index => $item)
                                    <li>
                                        <a href="{{ route('post.show', [$item->id, $item->slug]) }}"
                                           title="{{ $item->title }}" class="group flex gap-3">
                                            <span class="ar-rank {{ $index === 0 ? 'ar-rank--first' : '' }}">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="ar-clamp-2 text-sm leading-snug transition-colors group-hover:text-[color:var(--ar-accent)]">
                                                    {{ $item->title }}
                                                </span>
                                                <span class="mt-1 block text-xs text-gray-400">
                                                    <i class="far fa-eye mr-1"></i>{{ number_format((int) $item->count_view, 0, ',', ' ') }}
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif

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

        {{-- Archív kanála ako vodorovný pás. Stránkovanie tu delilo archív na
             strany, ktoré nikto neprelistoval — pás drží čitateľa pri článku
             a ďalšie dávky doťahuje na mieste. --}}
        <section class="mt-14" data-rail
                 data-rail-url="{{ route('post.rail', $post) }}"
                 data-rail-next="{{ optional($rail->nextCursor())->encode() }}">

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
                <div class="ar-rail-shell is-start" data-rail-shell>
                    <button type="button" class="ar-rail-nav ar-rail-nav--prev" data-rail-prev
                            aria-label="Posunúť späť" hidden>
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <div class="ar-rail" data-rail-track>
                        @include('posts._rail-items', ['items' => $rail])

                        {{-- Dlaždica na konci pásu. Bez skriptu je to obyčajný
                             odkaz na kanál, so skriptom doťahuje ďalšiu dávku
                             priamo do pásu. --}}
                        <a href="{{ $orgUrl }}" class="ar-rail-more" data-rail-more>
                            <span class="ar-rail-more__icon"><i class="fas fa-arrow-right"></i></span>
                            <span class="ar-rail-more__label" data-rail-more-label>Ďalšie príspevky</span>
                        </a>
                    </div>

                    <button type="button" class="ar-rail-nav ar-rail-nav--next" data-rail-next
                            aria-label="Posunúť ďalej">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                {{-- Koľko archívu už je za nami. Pás nemá posuvník, tak aspoň
                     takto vidno, že sa niekam ide. --}}
                <div class="ar-rail-progress mt-4"><span data-rail-bar></span></div>
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
        <script defer>
            new Plyr('#player');
        </script>
    @endif

    <script async defer crossorigin="anonymous"
            src="https://connect.facebook.net/sk_SK/sdk.js#xfbml=1&version=v5.0&appId=500741757380226"></script>

    <script>
        // Ukazovateľ prečítanej časti — počíta sa z výšky článku, nie stránky,
        // aby komentáre a archív pod ním neposúvali pruh predčasne na koniec.
        window.arReady(function () {
            var bar = document.querySelector('.js-reading-progress');
            var article = document.querySelector('article');
            if (!bar || !article) return;

            var update = function () {
                var span = article.offsetHeight - window.innerHeight;
                if (span <= 0) return;

                var ratio = (window.pageYOffset - article.offsetTop) / span;
                bar.style.width = Math.min(100, Math.max(0, ratio * 100)) + '%';
            };

            window.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
            update();
        });

        // Pás archívu kanála. Šípky posúvajú o šírku výrezu, doťahovanie beží
        // samo pred koncom pásu — kým čitateľ dojde k poslednej karte, ďalšia
        // dávka už v ňom je. Dlaždica na konci ostáva ako ručná poistka
        // (a bez skriptu ako obyčajný odkaz na kanál).
        window.arReady(function () {
            var rail = document.querySelector('[data-rail]');
            if (!rail) return;

            var shell = rail.querySelector('[data-rail-shell]');
            var track = rail.querySelector('[data-rail-track]');
            var more  = rail.querySelector('[data-rail-more]');
            if (!shell || !track || !more) return;

            var prev  = rail.querySelector('[data-rail-prev]');
            var next  = rail.querySelector('[data-rail-next]');
            var bar   = rail.querySelector('[data-rail-bar]');
            var label = rail.querySelector('[data-rail-more-label]');
            var icon  = more.querySelector('i');

            var url    = rail.dataset.railUrl;
            var cursor = rail.dataset.railNext || '';
            var busy   = false;
            var ticking = false;

            // Archív sa minul — dlaždica sa vráti k tomu, čím je bez skriptu:
            // odkazu na celý kanál.
            var finish = function () {
                cursor = '';
                more.classList.add('is-final');
                if (label) label.textContent = 'Zobraziť celý kanál';
            };

            // Necelý výrez, nech na okraji ostane rozčítaná karta ako stopa,
            // kde posun pokračuje.
            var step = function () { return Math.max(240, track.clientWidth * 0.85); };

            var paint = function () {
                var max  = track.scrollWidth - track.clientWidth;
                var left = track.scrollLeft;
                var atStart = left <= 4;
                var atEnd   = left >= max - 4;

                shell.classList.toggle('is-start', atStart);
                shell.classList.toggle('is-end', atEnd);
                if (prev) prev.hidden = atStart;
                if (next) next.hidden = atEnd && !cursor;
                if (bar)  bar.style.width = (max > 0 ? Math.min(100, (left / max) * 100) : 100) + '%';

                // Karta a kus navyše pred koncom — dávka stihne doraziť skôr,
                // než sa čitateľ doposúva na jej miesto.
                if (max - left < 400) load();
            };

            var load = function () {
                if (!cursor || busy) return;
                busy = true;
                more.classList.add('is-loading');
                if (icon) icon.className = 'fas fa-circle-notch fa-spin';

                fetch(url + '?cursor=' + encodeURIComponent(cursor), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error(response.status);
                        return response.json();
                    })
                    .then(function (data) {
                        more.insertAdjacentHTML('beforebegin', data.html);
                        cursor = data.next || '';

                        if (!cursor) finish();
                    })
                    .catch(function () {
                        // Ticho: v páse ostane, čo už je, a dlaždica vedie na
                        // kanál, takže sa čitateľ k zvyšku aj tak dostane.
                        finish();
                    })
                    .then(function () {
                        busy = false;
                        more.classList.remove('is-loading');
                        if (icon) icon.className = 'fas fa-arrow-right';
                        paint();
                    });
            };

            more.addEventListener('click', function (event) {
                if (!cursor) return;      // bez ďalšej dávky nech odkaz funguje
                event.preventDefault();
                load();
            });

            if (prev) prev.addEventListener('click', function () {
                track.scrollBy({ left: -step(), behavior: 'smooth' });
            });

            if (next) next.addEventListener('click', function () {
                track.scrollBy({ left: step(), behavior: 'smooth' });
            });

            track.addEventListener('scroll', function () {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(function () { ticking = false; paint(); });
            }, { passive: true });

            window.addEventListener('resize', paint);

            if (!cursor) finish();
            paint();
        });

        // Kopírovanie odkazu. clipboard API funguje len cez https, na http
        // sa tlačidlo správa ako keby sa nič nestalo — preto krátky fallback.
        window.arReady(function () {
            document.querySelectorAll('.js-copy-link').forEach(function (button) {
                button.addEventListener('click', function () {
                    var done = function () {
                        var original = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-check"></i> Skopírované';
                        setTimeout(function () { button.innerHTML = original; }, 2000);
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(button.dataset.url).then(done);
                        return;
                    }

                    var field = document.createElement('input');
                    field.value = button.dataset.url;
                    document.body.appendChild(field);
                    field.select();
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    document.body.removeChild(field);
                });
            });
        });
    </script>
@endpush
