{{-- Google Analytics 4. Zapína sa len v produkcii a keď je nastavené GA_MEASUREMENT_ID. --}}
@if (app()->isProduction() && ($gaId = config('services.google_analytics.measurement_id')))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}" nonce="{{ csp_nonce() }}"></script>
    <script nonce="{{ csp_nonce() }}">
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', @json($gaId), { anonymize_ip: true });
        {{-- Udalosť z kontrolera: with('ga_event', ['name' => ..., 'params' => [...]]) --}}
        @if ($gaEvent = session('ga_event'))
            gtag('event', @json($gaEvent['name']), @json($gaEvent['params'] ?? new stdClass));
        @endif
    </script>
@endif
