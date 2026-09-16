<?php

namespace App\Providers;

use App\Observers\PostObserver;
use App\Models\Post;
use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Nie env('APP_ENV') — po `php artisan config:cache` vracia env()
        // v produkcii null, takže by sa vývojársky balík registroval aj tam.
        if (! $this->app->environment('production')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();

        // Morph mapa 'App\Models\Organization' => Canal tu stála preto, že
        // favorites.favorited_type niesol staré meno modelu. Migrácia
        // 2026_09_16_120000_rename_organizations_to_canals tie riadky prepísala
        // na App\Models\Canal (aj ešte staršie App\Organization a App\Prayer,
        // ktoré mapa nepokrývala a favorited() im vracal null), takže alias
        // už nemá čo prekladať.
        Carbon::setLocale(config('app.locale'));
    }
}
