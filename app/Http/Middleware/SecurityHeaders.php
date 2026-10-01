<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bezpečnostné hlavičky a CSP s nonce pre inline <script>/<style>
 * (v šablónach csp_nonce()). Politika je v config/csp.php.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        app()->instance('csp.nonce', $nonce);

        $response = $next($request);
        $h = $response->headers;

        $h->set('X-Frame-Options', 'SAMEORIGIN');
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Vite dev server (HMR) by CSP porušoval, lokálne ju preto nenasadzujeme.
        if (! app()->environment('local')) {
            $h->set(
                config('csp.enforce') ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only',
                $this->policy($nonce)
            );
        }
        // frame-ancestors z Report-Only sa neuplatňuje, preto ide vždy zvlášť.
        if (config('csp.enforce') !== true) {
            $h->set('Content-Security-Policy', "frame-ancestors 'self'");
        }

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000');
        }

        $h->remove('X-Powered-By');

        return $response;
    }

    private function policy(string $nonce): string
    {
        return collect(config('csp.directives'))
            ->map(function (array $sources, string $directive) use ($nonce) {
                if (in_array($directive, ['script-src', 'style-src'], true)) {
                    $sources[] = "'nonce-{$nonce}'";
                }

                return $directive . ' ' . implode(' ', $sources);
            })
            ->implode('; ');
    }
}
