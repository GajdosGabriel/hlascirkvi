<?php

namespace App\Http\Middleware;

use App\Support\Seo;
use Closure;
use Illuminate\Http\Request;

/**
 * Natrvalo presmeruje každú nekanonickú adresu stránky na kanonickú.
 *
 * Hostiteľ: web odpovedal 200 aj na www.hlascirkvi.sk. Canonical síce
 * ukazoval na hlascirkvi.sk, no Search Console potom každú stránku hlásila
 * dvakrát („Alternatívna stránka so správnou kanonickou značkou"). Iný
 * hostiteľ než ten z config/seo.php preto presmeruje.
 *
 * Cesta: koreňový .htaccess prepisuje všetko do public/, takže web odpovedal
 * 200 aj na /public/…, /public/index.php/… a /index.php/…. Laravel z takej
 * adresy odvodí base URL (/public), ktorú potom pridá ku každému odkazu
 * na stránke vrátane canonical — stránka teda sama seba vyhlásila za
 * kanonickú a Google ju viedol ako „Duplikovať bez kánonickej adresy
 * vybranej používateľom". Do cache sa takto dostal aj feed s /public
 * odkazmi, ktorý potom videli aj návštevníci čistej adresy.
 *
 * Schému (http → https) zámerne nerieši: web beží za proxy a bez dôvery
 * k X-Forwarded-Proto by presmerovanie mohlo skončiť v slučke. To patrí
 * do nastavenia hostingu.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->isProduction() && $request->isMethodSafe() && $this->isNotCanonical($request)) {
            $query = $request->server('QUERY_STRING');

            return redirect()->away(
                Seo::canonicalUrl($request->getPathInfo() . ($query !== null && $query !== '' ? '?' . $query : '')),
                301
            );
        }

        return $next($request);
    }

    protected function isNotCanonical(Request $request): bool
    {
        if ($request->getBaseUrl() !== '') {
            return true;
        }

        $host = parse_url((string) config('seo.url'), PHP_URL_HOST);

        return $host && strcasecmp($request->getHost(), $host) !== 0;
    }
}
