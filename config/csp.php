<?php

return [
    /*
     * Content-Security-Policy. Kým je CSP_ENFORCE=false, politika ide len ako
     * Report-Only (prehliadač porušenia vypíše do konzoly, nič neblokuje).
     * Po vyčistení konzoly na ostrom webe sa prepne na true.
     *
     * 'unsafe-eval' vyžaduje Vue s kompilátorom šablón (vue.esm-bundler), lebo
     * komponenty sa píšu priamo do Blade. Odpadne po prechode na predkompilované
     * šablóny. Inline <script>/<style> chránia nonce, handlery sú v data-atribútoch.
     */
    'enforce' => (bool) env('CSP_ENFORCE', false),

    'directives' => [
        'default-src' => ["'self'"],
        'script-src' => ["'self'", "'unsafe-eval'", 'https://www.googletagmanager.com', 'https://www.google-analytics.com', 'https://rec.smartlook.com', 'https://www.youtube.com', 'https://s.ytimg.com'],
        'style-src' => ["'self'", 'https://fonts.googleapis.com'],
        // Atribúty style="" ostávajú; riziko je oproti skriptom nízke.
        'style-src-attr' => ["'unsafe-inline'"],
        'font-src' => ["'self'", 'data:', 'https://fonts.gstatic.com'],
        'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
        'media-src' => ["'self'", 'https:'],
        'connect-src' => ["'self'", 'https:', 'wss:'],
        'frame-src' => ["'self'", 'https://www.youtube.com', 'https://www.youtube-nocookie.com', 'https://www.facebook.com', 'https://www.google.com'],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'frame-ancestors' => ["'self'"],
    ],
];
