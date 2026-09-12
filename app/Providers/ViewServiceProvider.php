<?php

namespace App\Providers;


use App\Models\User;
use App\Models\Verse;
use App\Models\Category;
use Illuminate\Support\ServiceProvider;



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
        // Predný zoznam („Kresťanské osobnosti") si dáta berie sám —
        // <x-front-list-card /> cez App\Services\FrontList\FrontList.

        // Zrušené boli aj composery pre `canals.list-canals` a `posts.sviatok`.
        // Ani jeden z tých pohľadov nebol odnikiaľ vkladaný a oba si pýtali
        // príspevky podľa čísla updatera — `list-canals` dokonca podľa
        // updatera 4, ktorý sa k príspevkom nikdy nepriraďoval, takže vracal
        // vždy prázdno.

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

    }
}
