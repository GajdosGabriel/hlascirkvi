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
    {{-- Roboto drží telo textu spoločné so zvyškom webu, Bitter je serif pre
         nadpisy — od neho má výpis podujatí svoj vlastný, redakčný charakter. --}}
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Bitter:wght@500;600;700&family=Roboto:wght@300;400;500;700&display=swap">

    <link rel="stylesheet"
          href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
          integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
          crossorigin="anonymous">

    <script src="{{ asset('js/lazysizes.min.js') }}" async></script>

    {{-- Vue komponenty v hlavičke a v pätičke čítajú window.App; bez neho
         spadne celý bundle a s ním aj prihlasovacie menu. --}}
    <script>
        window.App = {!! json_encode([
            'csrfToken' => csrf_token(),
            'user' => Auth::user(),
            'signedIn' => Auth::check(),
            'baseUrl' => asset('/'),
        ]) !!};
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Vlastná paleta a stavebné prvky výpisu. Zámerne v layoute a nie
         v app.css: týka sa len podujatí a keď sa raz sekcia prekreslí,
         maže sa jeden súbor, nie riadky roztrúsené v globálnych štýloch. --}}
    <style>
        :root {
            --ev-paper:      #faf7f1;
            --ev-paper-deep: #f2ece1;
            --ev-ink:        #1c1917;
            --ev-ink-soft:   #57534e;
            --ev-line:       #e2d9c9;
            --ev-accent:     #b45309;
            --ev-accent-soft:#fdf1e0;
            --ev-night:      #17233f;
        }

        .ev-body {
            background-color: var(--ev-paper);
            /* Jemné zrno papiera — na bielom pozadí portálu Event nič také
               nie je, takže je to hneď vidieť pri porovnaní. */
            background-image:
                radial-gradient(circle at 15% 10%, rgba(180, 83, 9, .05), transparent 45%),
                radial-gradient(circle at 85% 0%, rgba(23, 35, 63, .06), transparent 40%);
            color: var(--ev-ink);
            font-family: Roboto, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        .ev-display {
            font-family: Bitter, Georgia, "Times New Roman", serif;
            letter-spacing: -.01em;
        }

        /* Nadpis sekcie s linkou dopravo — nahrádza obyčajné <h2>. */
        .ev-rule {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .ev-rule::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--ev-line);
        }

        /* ---- Časová os -------------------------------------------------- */

        .ev-timeline {
            position: relative;
        }
        /* Chrbtica osi. Na mobile by len kradla šírku, preto až od md. */
        @media (min-width: 768px) {
            .ev-timeline::before {
                content: "";
                position: absolute;
                left: 3.25rem;
                top: .5rem;
                bottom: 3rem;
                width: 2px;
                background: linear-gradient(180deg, var(--ev-line), rgba(226, 217, 201, 0));
            }
        }

        /* Dátumová pečiatka — mierne natočená, ako odtlačok v kalendári. */
        .ev-stamp {
            background: #fff;
            border: 1px solid var(--ev-line);
            box-shadow: 0 1px 0 rgba(28, 25, 23, .04), 0 6px 18px -12px rgba(28, 25, 23, .5);
            transform: rotate(-2deg);
            transition: transform .2s ease;
        }
        .ev-stamp-today {
            border-color: var(--ev-accent);
            background: var(--ev-accent-soft);
        }

        .ev-card {
            background: #fff;
            border: 1px solid var(--ev-line);
            transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
        }
        .ev-card:hover {
            border-color: #d6c9b1;
            box-shadow: 0 18px 40px -28px rgba(28, 25, 23, .55);
            transform: translateY(-2px);
        }

        /* Výplň namiesto plagátu — podujatia z importu ho často nemajú. */
        .ev-noposter {
            background:
                repeating-linear-gradient(135deg, #f6efe3 0 10px, #f1e8d8 10px 20px);
            color: #b9a888;
        }

        /* ---- Stena plagátov --------------------------------------------- */

        .ev-wall {
            column-gap: 1.25rem;
        }
        @media (min-width: 640px)  { .ev-wall { column-count: 2; } }
        @media (min-width: 1024px) { .ev-wall { column-count: 3; } }
        .ev-wall > * {
            break-inside: avoid;
            margin-bottom: 1.25rem;
        }
        /* Plagáty majú rôznu výšku a rozmery sa dozvieme až po načítaní.
           Bez rezervovaného miesta má obrázok nulovú výšku a dátumová
           pečiatka nad ním dosadne na nadpis pod obrázkom. */
        .ev-wall img {
            display: block;
            min-height: 11rem;
            background: var(--ev-paper-deep);
        }

        /* ---- Text detailu ------------------------------------------------ */

        .ev-prose { color: #33302c; line-height: 1.75; }
        .ev-prose h2,
        .ev-prose h3,
        .ev-prose h4 {
            font-family: Bitter, Georgia, serif;
            color: var(--ev-ink);
            font-weight: 600;
            margin: 1.75rem 0 .6rem;
            line-height: 1.3;
        }
        .ev-prose h2 { font-size: 1.35rem; }
        .ev-prose h3 { font-size: 1.15rem; }
        .ev-prose h4 { font-size: 1.02rem; }
        .ev-prose p  { margin-bottom: 1rem; }
        .ev-prose ul,
        .ev-prose ol { margin: 0 0 1rem 1.25rem; }
        .ev-prose ul { list-style: disc; }
        .ev-prose ol { list-style: decimal; }
        .ev-prose li { margin-bottom: .35rem; }
        .ev-prose a  { color: var(--ev-accent); text-decoration: underline; }
        .ev-prose blockquote {
            border-left: 3px solid var(--ev-accent);
            padding-left: 1rem;
            color: var(--ev-ink-soft);
            font-style: italic;
            margin: 1.25rem 0;
        }
        .ev-prose hr { border-color: var(--ev-line); margin: 1.5rem 0; }

        /* Odkaz s podčiarknutím, ktoré sa vykreslí až pri prejdení myšou. */
        .ev-link { background-image: linear-gradient(currentColor, currentColor);
            background-size: 0 1px; background-repeat: no-repeat;
            background-position: 0 100%; transition: background-size .25s ease; }
        .ev-link:hover { background-size: 100% 1px; }

        /* Stránkovanie z Laravelu prichádza s vlastnou triedou; zjemníme ho,
           nech neznie inou farbou než zvyšok sekcie. */
        .ev-pagination svg { height: 1rem; width: 1rem; }
    </style>

    @stack('head')
</head>
<body class="ev-body">

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
