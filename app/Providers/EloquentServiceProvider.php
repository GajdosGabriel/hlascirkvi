<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use App\Models\Event;
use App\Models\Prayer;
use App\Models\Comment;
use App\Models\BigThink;
use App\Models\Messenger;
use App\Models\Organization;
use App\Models\EventSubscribe;
use App\Observers\PostObserver;
use App\Observers\UserObserver;
use App\Observers\EventObserver;
use App\Observers\PrayerObserver;
use App\Observers\CommentObserver;
use App\Observers\BigThinkObserver;
use App\Observers\MessengerObserver;
use App\Observers\OrganizationObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\EventSubscribeObserver;



class EloquentServiceProvider extends ServiceProvider
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
        User::observe(UserObserver::class);
        Post::observe(PostObserver::class);
        Comment::observe(CommentObserver::class);
        Messenger::observe(MessengerObserver::class);
        Event::observe(EventObserver::class);
        Organization::observe(OrganizationObserver::class);
        BigThink::observe(BigThinkObserver::class);
        Prayer::observe(PrayerObserver::class);
        EventSubscribe::observe(EventSubscribeObserver::class);

    }
}
