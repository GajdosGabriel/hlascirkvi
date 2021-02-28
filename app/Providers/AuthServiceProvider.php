<?php

namespace App\Providers;

use App\Policies\PrayerPolicy;
use App\Prayer;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Prayer::class => PrayerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        \Gate::before( function ()
        {
            if( \Auth::user()->email == env('ADMIN_EMAIL')  ) return true;

        });
    }
}
