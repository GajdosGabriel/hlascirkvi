<?php

namespace App\Providers;


use App\Models\Post;
use App\Models\User;

use App\Models\Verse;
use App\Models\BigThink;
use App\Models\Category;
use App\Models\Organization;
use Illuminate\Support\ServiceProvider;

use App\Repositories\Eloquent\EloquentPostRepository;
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
                ->where('title', 'like', '%vianoce%')
                // ->OrWhere('title', 'like', '%ducha sv%')
                // ->orWhere('title', 'like', '%turic%')


                // ->where('title', 'like', '%duch sv%')
                // ->OrWhere('title', 'like', '%ducha sv%')
                // ->orWhere('title', 'like', '%turic%')
                ->whereNotIn('id', [10606]) // Zdvojené video
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

    }
}
