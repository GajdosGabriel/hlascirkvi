<?php

namespace App\Providers;


use App\Models\Verse;
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
        // Predný zoznam („Kresťanské osobnosti", „Cirkvi a spoločenstvá") si
        // dáta berie sám — <x-front-list-card type="…" /> cez
        // App\Services\FrontList\FrontList.

        // Zrušené boli aj composery pre `canals.list-canals` a `posts.sviatok`.
        // Ani jeden z tých pohľadov nebol odnikiaľ vkladaný a oba si pýtali
        // príspevky podľa čísla updatera — `list-canals` dokonca podľa
        // updatera 4, ktorý sa k príspevkom nikdy nepriraďoval, takže vracal
        // vždy prázdno.

        // Composery pre `posts.form` (všetci používatelia a kategórie) sú
        // preč — formulár článku ani jednu z tých premenných nečíta, takže sa
        // pri každom otvorení formulára zbytočne načítala celá tabuľka users.

        // Verses daily reading
        view()->composer('verses.daily-modul', function ($view) {
            $view->with('verse', Verse::today());
        });

    }
}
