{{-- Google Analytics 4. Zapína sa len v produkcii a keď je nastavené GA_MEASUREMENT_ID. --}}
@if (app()->isProduction() && ($gaId = config('services.google_analytics.measurement_id')))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}" nonce="{{ csp_nonce() }}"></script>
    <script nonce="{{ csp_nonce() }}">
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', @json($gaId), { anonymize_ip: true });
        {{-- Kliky v hlavnej navigácii (desktop aj mobilné menu): udalosť nav_click s textom odkazu. --}}
        document.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('nav[aria-label="Hlavná navigácia"] a[href]');
            if (!a) return;
            gtag('event', 'nav_click', {
                link_text: (a.textContent || '').replace(/\s+/g, ' ').replace(/\s\d+$/, '').trim().slice(0, 60),
                link_url: a.pathname,
                menu: a.closest('.lg\\:flex') ? 'desktop' : 'mobile'
            });
        }, true);
        {{-- Podujatia: otvorenie detailu (/akcie/{id}/{slug}) a prvky s data-ga-event
             (lístky, kalendár). Meno podujatia sa berie z textu odkazu alebo zo slugu. --}}
        document.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a[href]');
            if (!a || a.closest('nav[aria-label="Hlavná navigácia"]')) return;
            var tagged = a.closest('[data-ga-event]');
            if (tagged) {
                gtag('event', tagged.getAttribute('data-ga-event'), {
                    event_title: (tagged.getAttribute('data-ga-label') || '').slice(0, 100)
                });
                return;
            }
            var m = a.pathname.match(/^\/akcie\/(\d+)(?:\/([^\/]+))?/);
            if (!m) return;
            var text = (a.textContent || '').replace(/\s+/g, ' ').trim();
            gtag('event', 'event_open', {
                event_id: m[1],
                event_title: (text || a.getAttribute('aria-label') || m[2] || m[1]).slice(0, 100),
                source: location.pathname.indexOf('/akcie/') === 0 && /\/akcie\/\d+/.test(location.pathname) ? 'detail' : 'list'
            });
        }, true);
        {{-- Udalosť z kontrolera: with('ga_event', ['name' => ..., 'params' => [...]]) --}}
        @if ($gaEvent = session('ga_event'))
            gtag('event', @json($gaEvent['name']), @json($gaEvent['params'] ?? new stdClass));
        @endif
    </script>
@endif
