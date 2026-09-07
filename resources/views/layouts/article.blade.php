<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('title')
    @yield('meta')

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

    {{-- Paleta a stavebné prvky detailu príspevku. Zámerne tu a nie v app.css:
         týka sa len tejto sekcie a keď sa raz prekreslí, maže sa jeden súbor. --}}
    <style>
        :root {
            --ar-paper:       #f6f6f7;
            --ar-paper-deep:  #ececee;
            --ar-ink:         #101828;
            --ar-ink-soft:    #545a67;
            --ar-line:        #e3e4e8;
            --ar-accent:      #b91c1c;
            --ar-accent-soft: #fdf1f1;
        }

        .ar-body {
            background-color: var(--ar-paper);
            color: var(--ar-ink);
            font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        /* Nadpisy a čísla — úzke, bezpätkové, s tesným prestrkom. */
        .ar-display {
            font-family: Inter, system-ui, -apple-system, sans-serif;
            letter-spacing: -.022em;
        }

        /* Nadradený štítok nad titulkom (názov kanála). */
        .ar-kicker {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--ar-accent);
        }

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

        .ar-card {
            background: #fff;
            border: 1px solid var(--ar-line);
            transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
        }
        .ar-card:hover {
            border-color: #cfd2da;
            box-shadow: 0 18px 40px -30px rgba(16, 24, 40, .6);
            transform: translateY(-2px);
        }

        /* Náhrada obrázka pri príspevkoch bez fotky aj bez avatara kanála. */
        .ar-noimage {
            background: linear-gradient(135deg, var(--ar-paper-deep), #f7f7f8);
            color: #b3b7c0;
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

        /* ---- Drobnosti ---------------------------------------------------- */

        .ar-rule { display: flex; align-items: center; gap: 1rem; }
        .ar-rule::after { content: ""; flex: 1; height: 1px; background: var(--ar-line); }

        .ar-link {
            background-image: linear-gradient(currentColor, currentColor);
            background-size: 0 1px;
            background-repeat: no-repeat;
            background-position: 0 100%;
            transition: background-size .25s ease;
        }
        .ar-link:hover { background-size: 100% 1px; }
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

    @stack('scripts')
</body>
</html>
