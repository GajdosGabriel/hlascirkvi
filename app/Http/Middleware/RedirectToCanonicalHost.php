<?php

namespace App\Http\Middleware;

use App\Support\Seo;
use Closure;
use Illuminate\Http\Request;

/**
 * Web odpovedal 200 aj na www.hlascirkvi.sk. Canonical síce ukazoval na
 * hlascirkvi.sk, no Search Console potom každú stránku hlásila dvakrát
 * („Alternatívna stránka so správnou kanonickou značkou"). Iný hostiteľ než
 * ten z config/seo.php preto natrvalo presmeruje.
 *
 * Schému (http → https) zámerne nerieši: web beží za proxy a bez dôvery
 * k X-Forwarded-Proto by presmerovanie mohlo skončiť v slučke. To patrí
 * do nastavenia hostingu.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next)
    {
        $host = parse_url((string) config('seo.url'), PHP_URL_HOST);

        if (app()->isProduction()
            && $host
            && $request->isMethodSafe()
            && strcasecmp($request->getHost(), $host) !== 0) {
            return redirect()->away(Seo::canonicalUrl($request->getRequestUri()), 301);
        }

        return $next($request);
    }
}
