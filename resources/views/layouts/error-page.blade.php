{{-- Samostatný rám funguje aj bez databázy a Vite manifestu. --}}
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('error-title') – Hlas Cirkvi</title>
    @include('partials.design-system')
    <style nonce="{{ csp_nonce() }}">
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; }
        a { text-decoration: none; }
        a:focus-visible { outline: 3px solid var(--ar-accent); outline-offset: 5px; }
        .error-header { border-bottom: 1px solid var(--ar-line); background: #fff; padding: 1.25rem max(1.25rem, calc((100vw - 1152px) / 2)); }
        .error-brand { color: var(--ar-ink); font-size: 1.25rem; font-weight: 800; letter-spacing: -.04em; }
        .error-brand span { color: var(--ar-accent); }
        .error-main { flex: 1; display: grid; place-items: center; padding: 3.5rem 1.25rem; }
        .error-card { width: 100%; max-width: 760px; padding: clamp(1.5rem, 5vw, 4rem); border: 1px solid var(--ar-line); border-radius: 1.5rem; background: #fff; text-align: center; box-shadow: 0 24px 60px -40px rgba(16,24,40,.3); }
        .error-code { margin: 0 0 1.5rem; color: var(--ar-accent); font-size: clamp(5rem, 18vw, 9rem); font-weight: 800; line-height: 1; letter-spacing: -.07em; }
        .error-card h1 { margin: .75rem 0 1rem; font-size: clamp(1.6rem, 4vw, 2.5rem); line-height: 1.2; }
        .error-description { max-width: 460px; margin: 0 auto; color: var(--ar-ink-soft); line-height: 1.75; overflow-wrap: anywhere; }
        .error-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .75rem; margin-top: 2rem; }
        .error-actions .ar-btn { min-height: 44px; padding: .75rem 1.25rem; white-space: normal; }
        .error-footer { padding: 1.5rem; text-align: center; color: var(--ar-ink-soft); font-size: .8125rem; }
        @media (max-width: 480px) { .error-main { padding: 2rem 1rem; } .error-actions { flex-direction: column; } }
    </style>
</head>
<body class="ar-body">
    <header class="error-header"><a class="error-brand" href="{{ url('/') }}">Hlas<span>Cirkvi</span>.sk</a></header>
    <main class="error-main" id="obsah">
        <section class="error-card" aria-labelledby="error-title">
            <p class="error-code ar-display" aria-label="Chyba @yield('error-code')">@yield('error-code')</p>
            <p class="ar-kicker">Hlas Cirkvi</p>
            <h1 id="error-title" class="ar-display">@yield('error-title')</h1>
            <p class="error-description">@yield('error-description')</p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="ar-btn ar-btn--accent">Prejsť na úvodnú stránku</a>
                @yield('error-action')
            </div>
        </section>
    </main>
    <footer class="error-footer">Hlas Cirkvi · Priestor pre vieru a spoločenstvo</footer>
</body>
</html>
