<?php

namespace App\Providers;

use App\Observers\PostObserver;
use App\Models\Post;
use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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

        RateLimiter::for('guest-writes', fn (Request $request) => [
            Limit::perMinute(10)->by('m:'.$request->ip()),
            Limit::perHour(30)->by('h:'.$request->ip()),
        ]);

        // Prihlásený sa počíta podľa účtu (nie IP), hosť prísnejšie podľa IP —
        // jeho pokus spúšťa odoslanie e-mailu.
        RateLimiter::for('favorites', fn (Request $request) => $request->user()
            ? Limit::perMinute(60)->by('u:'.$request->user()->id)
            : Limit::perMinute(10)->by('i:'.$request->ip()));

        // Jedno pravidlo pre registráciu aj obnovu hesla (ResetPasswordController
        // z laravel/ui siaha na Password::defaults()).
        Password::defaults(fn () => Password::min(8)->uncompromised());

        // Morph mapa 'App\Models\Organization' => Canal tu stála preto, že
        // favorites.favorited_type niesol staré meno modelu. Migrácia
        // 2026_09_16_120000_rename_organizations_to_canals tie riadky prepísala
        // na App\Models\Canal (aj ešte staršie App\Organization a App\Prayer,
        // ktoré mapa nepokrývala a favorited() im vracal null), takže alias
        // už nemá čo prekladať.
        Carbon::setLocale(config('app.locale'));
    }
}
