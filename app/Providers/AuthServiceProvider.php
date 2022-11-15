<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Prayer;
use App\Models\Seminar;
use App\Models\Organization;
use App\Policies\PrayerPolicy;
use App\Policies\SeminarPolicy;
use App\Policies\EventPolicy;
use App\Policies\OrganizationPolicy;
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
        Event::class => EventPolicy::class,
        Organization::class => OrganizationPolicy::class,
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
