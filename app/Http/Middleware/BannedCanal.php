<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;

/**
 * Príspevky z vypnutého kanála sa nezobrazujú.
 *
 * Telo tejto triedy bolo celé zakomentované, hoci middleware ostal nasadený
 * na post.show aj post.rail — budil teda dojem ochrany, ktorú nerobil.
 * Samotná kontrola žila dvakrát nakopírovaná v Public\PostController.
 */
class BannedCanal
{
    public function handle(Request $request, Closure $next)
    {
        $post = $request->route('post');

        // Kanál bez príznaku `published` je pre verejnosť neexistujúci — 404,
        // nie 405. „Method Not Allowed" hovorí crawlerom niečo úplne iné.
        if ($post instanceof Post && ! $post->organization?->published) {
            abort(404);
        }

        return $next($request);
    }
}
