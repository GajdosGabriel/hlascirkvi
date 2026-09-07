<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.meta')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Opačná dvojica než v podujatiach: tam serifový nadpis nad Robotom,
         tu bezpätkový nadpis (Inter) nad serifovým telom textu (Newsreader).
         Príspevok sa hlavne číta, preto serif dostal telo a nie hlavičku. --}}
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&display=swap">

    <link rel="stylesheet"
          href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
          integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
          crossorigin="anonymous">

    <script src="{{ asset('js/lazysizes.min.js') }}" async></script>

    {{-- Vue komponenty v hlavičke, v článku aj v pätičke čítajú window.App;
         bez neho spadne celý bundle a s ním aj komentáre a prihlásenie. --}}
    <script>
        window.App = {!! json_encode([
            'csrfToken' => csrf_token(),
            'user' => Auth::user(),
            'signedIn' => Auth::check(),
            'baseUrl' => asset('/'),
        ]) !!};
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.design-system')

    {{-- Prvky, ktoré nosí len detail príspevku. Zámerne tu a nie v app.css:
         týkajú sa jednej šablóny a keď sa raz prekreslí, maže sa jeden blok. --}}
    <style>
        /* Ukazovateľ prečítanej časti článku. Šírku dopĺňa skript v šablóne. */
        .ar-progress {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            width: 0;
            background: var(--ar-accent);
            z-index: 60;
            transition: width .1s linear;
        }

        /* ---- Telo článku -------------------------------------------------- */

        .ar-prose {
            font-family: Newsreader, Georgia, "Times New Roman", serif;
            font-size: 1.175rem;
            line-height: 1.75;
            color: #22262f;
            /* Popisy z YouTube nosia dlhé odkazy bez medzier. */
            overflow-wrap: break-word;
        }
        .ar-prose h2,
        .ar-prose h3,
        .ar-prose h4 {
            font-family: Inter, system-ui, sans-serif;
            letter-spacing: -.02em;
            color: var(--ar-ink);
            font-weight: 700;
            margin: 2rem 0 .6rem;
            line-height: 1.25;
        }
        .ar-prose h2 { font-size: 1.4rem; }
        .ar-prose h3 { font-size: 1.18rem; }
        .ar-prose h4 { font-size: 1.05rem; }
        .ar-prose p  { margin-bottom: 1.15rem; }
        .ar-prose ul,
        .ar-prose ol { margin: 0 0 1.15rem 1.4rem; }
        .ar-prose ul { list-style: disc; }
        .ar-prose ol { list-style: decimal; }
        .ar-prose li { margin-bottom: .4rem; }
        .ar-prose a  { color: var(--ar-accent); text-decoration: underline; }
        .ar-prose img { max-width: 100%; height: auto; border-radius: .375rem; margin: 1.25rem 0; }
        .ar-prose iframe { max-width: 100%; }
        .ar-prose blockquote {
            font-style: italic;
            color: var(--ar-ink-soft);
            border-left: 3px solid var(--ar-accent);
            padding-left: 1.1rem;
            margin: 1.5rem 0;
        }
        .ar-prose hr { border-color: var(--ar-line); margin: 1.75rem 0; }
        /* Redakčný text z importu nosí vlastné šírky tabuliek. */
        .ar-prose table { width: 100%; display: block; overflow-x: auto; }

        /* Holý text z importu si nesie zalomenia riadkov v samotnom reťazci;
           bez pre-line by sa celý popis zlial do jedného bloku. */
        .ar-prose--plain { white-space: pre-line; }

        /* Prvé písmeno prvého odstavca — iniciálka ako v tlači. */
        .ar-prose--drop > p:first-of-type::first-letter {
            float: left;
            font-size: 3.4rem;
            line-height: .82;
            padding: .28rem .6rem 0 0;
            font-weight: 600;
            color: var(--ar-accent);
        }

        /* ---- Prehrávač ---------------------------------------------------- */

        /* Pomer 16:9 nesie samotný iframe, nie obal. Plyr sa načítava až po
           CSS a jeho `.plyr{position:relative}` by prebilo absolútne
           umiestnenie v obale — výsledkom by bol prehrávač dvojnásobnej
           výšky. Po inicializácii má prednosť pravidlo Plyru
           `.plyr__video-embed iframe`, takže sa nič nebije. */
        .ar-player { background: #000; }
        .ar-player iframe {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            border: 0;
        }

        /* ---- Pás archívu kanála -------------------------------------------- */

        .ar-rail-shell { position: relative; }

        .ar-rail {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            scroll-snap-type: x proximity;
            scroll-behavior: smooth;
            /* Pás sa ovláda šípkami a ťahom, stav nesie prúžok pod ním —
               systémová lišta by len rozbíjala rad kariet. */
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: .25rem;
        }
        .ar-rail::-webkit-scrollbar { display: none; }

        .ar-rail > * {
            scroll-snap-align: start;
            /* Karty držia pevnú šírku, aby posun o výrez vždy skončil na
               celej karte a nie uprostred textu. Posledná je zámerne len
               načatá — je to jediná stopa, že pás pokračuje. */
            flex: 0 0 46%;
        }
        @media (min-width: 640px) { .ar-rail > * { flex-basis: 30%; } }
        @media (min-width: 768px) { .ar-rail > * { flex-basis: 23%; } }
        @media (min-width: 1024px) { .ar-rail > * { flex-basis: 18.4%; } }

        /* Zmiznutie kariet pod okrajom namiesto tvrdého orezu. Kryje sa
           s farbou papiera, takže pás vyzerá, že pokračuje mimo stránky. */
        .ar-rail-shell::before,
        .ar-rail-shell::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            width: 3rem;
            pointer-events: none;
            z-index: 4;
            opacity: 1;
            transition: opacity .2s ease;
        }
        .ar-rail-shell::before {
            left: 0;
            background: linear-gradient(to right, var(--ar-paper), rgba(246, 246, 247, 0));
        }
        .ar-rail-shell::after {
            right: 0;
            background: linear-gradient(to left, var(--ar-paper), rgba(246, 246, 247, 0));
        }
        .ar-rail-shell.is-start::before,
        .ar-rail-shell.is-end::after { opacity: 0; }

        .ar-rail-nav {
            position: absolute;
            top: 50%;
            z-index: 5;
            display: flex;
            width: 2.5rem;
            height: 2.5rem;
            align-items: center;
            justify-content: center;
            transform: translateY(-50%);
            border: 1px solid var(--ar-line);
            border-radius: 9999px;
            background: #fff;
            color: var(--ar-ink);
            box-shadow: 0 12px 28px -14px rgba(16, 24, 40, .55);
            opacity: 0;
            transition: opacity .2s ease, border-color .15s ease, color .15s ease;
        }
        .ar-rail-shell:hover .ar-rail-nav,
        .ar-rail-nav:focus-visible { opacity: 1; }
        .ar-rail-nav:hover { border-color: #cfd2da; color: var(--ar-accent); }
        .ar-rail-nav[hidden] { display: none; }
        .ar-rail-nav--prev { left: -.9rem; }
        .ar-rail-nav--next { right: -.9rem; }
        /* Na dotyku sa pás ťahá prstom a šípky by len zakrývali karty. */
        @media (hover: none) { .ar-rail-nav { display: none; } }

        /* Dlaždica na konci pásu — miesto tlačidla pod výpisom stojí v rade
           kariet, takže "ďalej" je tam, kde posun aj tak končí. */
        .ar-rail-more {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            border: 1px dashed #d3d6dd;
            border-radius: .5rem;
            background: #fff;
            color: var(--ar-ink-soft);
            text-align: center;
            padding: 1rem;
            transition: border-color .2s ease, color .2s ease;
        }
        .ar-rail-more:hover { border-color: var(--ar-accent); color: var(--ar-accent); }
        .ar-rail-more__icon {
            display: flex;
            width: 2.75rem;
            height: 2.75rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: var(--ar-accent-soft);
            color: var(--ar-accent);
        }
        .ar-rail-more__label { font-size: .8125rem; font-weight: 600; }
        .ar-rail-more.is-loading { border-style: solid; }

        .ar-rail-progress {
            height: 2px;
            border-radius: 2px;
            background: var(--ar-line);
            overflow: hidden;
        }
        .ar-rail-progress span {
            display: block;
            width: 0;
            height: 100%;
            background: var(--ar-accent);
            transition: width .15s ease;
        }

        /* .ar-rank pre panel "Naj z kanála" prešiel do partials/design-system —
           rovnaký rebríček nesie aj profil kanála pod layouts/app. */
    </style>

    @stack('head')
</head>
<body class="ar-body">

    @can('admin')
    @else
        @include('partials.analyticstracking')
    @endcan

    <div id="app">
        <x-navigation.main-menu />

        <main>
            @include('layouts.errors')

            @yield('content')

            <notification message="{{ session('flash') }}"></notification>
        </main>

        @include('layouts.footer')
    </div>

    {{-- Skripty šablón bežia počas parsovania stránky, ale Vue vzápätí
         prekreslí celý #app a pôvodné uzly aj s ich poslucháčmi zahodí.
         arReady() ich preto podrží a spustí až nad hotovým stromom.
         window.load je poistka pre prípad, že by bundle nenabehol. --}}
    <script>
        (function () {
            var pending = [];
            var started = false;

            var start = function () {
                if (started) return;
                started = true;
                pending.forEach(function (fn) { fn(); });
                pending = [];
            };

            window.arReady = function (fn) { started ? fn() : pending.push(fn); };

            document.addEventListener('app:ready', start);
            window.addEventListener('load', start);
        })();
    </script>

    @stack('scripts')
</body>
</html>
