<?php

namespace App\Providers;


use App\Post;
use App\User;

use App\Event;
use App\Verse;
use App\BigThink;
use App\Category;
use Carbon\Carbon;
use App\Organization;
use App\Filters\EventFilters;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Repositories\Eloquent\EloquentOrganizationRepository;



class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('organizations.list-users', function ($view) {
            $view->with(
                'users',

                (new EloquentOrganizationRepository())->frontOrganizationsList()
                ->orderBy('title', 'asc')->get()
            );
        });

        view()->composer('organizations.list-organizations', function ($view) {
            $view->with(
                'organizations',
                (new EloquentPostRepository())->getPostsByUpdater(4)
            );
        });

        // Sviatky modul
        view()->composer('posts.sviatok', function ($view) {
            $view->with(
                'videos',
                (new EloquentPostRepository())->postsByUpdater(15)
                ->where('title', 'like', '%advent%')
                // ->OrWhere('title', 'like', '%ducha sv%')
                // ->orWhere('title', 'like', '%turic%')


                // ->where('title', 'like', '%duch sv%')
                // ->OrWhere('title', 'like', '%ducha sv%')
                // ->orWhere('title', 'like', '%turic%')
                ->whereNotIn('id', [840]) // Zdvojené video
                ->get()->random(10)
            );
        });

        view()->composer('posts.form', function ($view) {
            $view->with('users', User::orderBy('last_name', 'asc')->get());
        });

        view()->composer('posts.form', function ($view) {
            $view->with('categories', Category::all());
        });

        // Verses daily reading
        view()->composer('verses.daily-modul', function ($view) {
            $view->with('verse', Verse::whereId(now()->dayOfYear)->first());
        });


        //  Big Thing
        view()->composer('bigthink.aside_last_big_think', function ($view) {
            $view->with('bigThings', BigThink::latest()->take(7)->get());
        });

        //  Active Events
        view()->composer('events.aside_modul', function ($view) {
            $view->with('events', (new EloquentEventRepository)->orderByStarting()->take(5)->get());
        });

        //  Curently Events
        view()->composer('events._current_events', function ($view) {
            $view->with('currentlyEvents', Event::where('start_at', '<=', Carbon::now())
                ->where('end_at', '>=', Carbon::now())
                ->wherePublished(1)
                ->orderBy('end_at', 'asc')
                ->get());
        });

        //  District Events count
        view()->composer('events.districts_modul', function ($view) {
            $view->with(
                'districts',
                $districts = DB::table('events')
                    ->where('end_at', '>', Carbon::now())->wherePublished(1)
                    ->where('deleted_at', null)
                    ->join('villages', 'events.village_id', '=', 'villages.id')
                    ->join('districts', 'districts.id', '=', 'villages.district_id')
                    ->select('districts.name', 'districts.id')
                    ->orderBy('districts.name', 'asc')
                    ->get()
                    ->groupBy('name')
            );
        });
    }
}
