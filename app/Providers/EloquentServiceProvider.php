<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\BigThink;
use App\Comment;
use App\Event;
use App\Messenger;
use App\Observers\BigThinkObserver;
use App\Observers\EventObserver;
use App\Observers\MessengerObserver;
use App\Observers\OrganizationObserver;
use App\Organization;
use App\User;
use App\Post;
use App\Observers\UserObserver;
use App\Observers\PostObserver;
use App\Observers\CommentObserver;


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
    }
}
