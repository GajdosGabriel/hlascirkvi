<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Prayer;
use App\Models\Seminar;
use App\Models\User;
use App\Models\Organization;
use App\Policies\PostPolicy;
use App\Policies\PrayerPolicy;
use App\Policies\SeminarPolicy;
use App\Policies\CommentPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Prayer::class => PrayerPolicy::class,
        Seminar::class => SeminarPolicy::class,
        Organization::class => OrganizationPolicy::class,
        Post::class => PostPolicy::class,
        User::class => UserPolicy::class,
        Comment::class => CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Explicitná schopnosť pre @can('superadmin') v šablónach. Doteraz to
        // nebola definovaná ability a prechádzalo to len vďaka Gate::before
        // nižšie, takže v kóde nebolo vidieť, čo vlastne kontroluje.
        Gate::define('superadmin', fn (User $user) => $user->hasRole('superadmin'));
        Gate::define('admin', fn (User $user) => $user->hasAnyRole(['admin', 'superadmin']));

        // Superadmin obchádza všetky policy.
        //
        // Predtým sa to určovalo porovnaním e-mailu s env('ADMIN_EMAIL') priamo
        // za behu. Po `php artisan config:cache` vracia env() v produkcii null,
        // takže superadmin ticho stratil práva — a užívateľ bez e-mailu (OAuth
        // vetva v Auth\AuthController) by ich naopak dostal.
        //
        // Musí vrátiť null, nie false, inak by callback všetko ostatné zamietol.
        Gate::before(fn (User $user) => $user->hasRole('superadmin') ? true : null);
    }
}
