<?php

namespace App\Providers;

use App\Post;
use App\User;
use App\Event;
use App\Prayer;
use App\Comment;
use App\BigThink;
use App\Messenger;
use App\Organization;
use App\Observers\PostObserver;
use App\Observers\UserObserver;
use App\Observers\EventObserver;
use App\Observers\PrayerObserver;
use App\Observers\CommentObserver;
use App\Observers\BigThinkObserver;
use App\Observers\MessengerObserver;
use App\Observers\OrganizationObserver;
use Illuminate\Support\ServiceProvider;


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
    }
}
