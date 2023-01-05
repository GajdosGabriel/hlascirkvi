<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BannedOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // $post = $request->route('post');
        // // Kanál(organization) nie je publikovaný a tým aj posts nie je možné zobrazovať.
        // if (!$post->organization->published) {
        //     abort(405, "Kanál {$post->organization->title} je vypnutý!");
        // }
        return $next($request);
    }
}
