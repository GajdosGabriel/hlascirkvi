<?php

namespace App\Http\Middleware;

use App\Repositories\Contracts\PostRepository;
use App\Services\CreditUser;
use Carbon\Carbon;
use Closure;

class SetCache
{

    public function __construct(PostRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Pripísanie bodov za dennú návštevu webu
//        (new CreditUser())->setVisitPage();

        if( session()->has('lastVisit')) {
            $this->repository->countUnwatchedSundayServicesVideos();
        } else {
          session()->put('lastVisit', Carbon::now());
        }

        if( isset(auth()->user()->set_denomination )) {
            session()->put('denomination', auth()->user()->set_denomination);
        }
        return $next($request);
    }
}
