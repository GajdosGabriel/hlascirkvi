<?php

namespace App\Providers;

use App\Events\VisitModel;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Listeners\SystemLogSubscriber;
use App\Listeners\ViewCounterListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        VisitModel::class => [
            ViewCounterListener::class,
        ],
    ];

    /**
     * Denník udalostí (admin → Denník): maily, zlyhané joby, prihlásenia, cron.
     *
     * @var array
     */
    protected $subscribe = [
        SystemLogSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
