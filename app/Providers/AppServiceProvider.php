<?php

namespace App\Providers;

use App\Observers\PostObserver;
use App\Models\Post;
use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        // Kanál sa do 9/2026 volal App\Models\Organization a pod týmto menom je
        // uložený v polymorfných stĺpcoch (favorites.favorited_type, images,
        // comments, views, notifications). Alias drží staré dáta čitateľné aj
        // nové zápisy konzistentné bez migrácie dát.
        Relation::morphMap([
            'App\Models\Organization' => \App\Models\Canal::class,
        ]);
        Carbon::setLocale(config('app.locale'));
    }
}
