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
        Carbon::setLocale(config('app.locale'));
    }
}
