<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\EventRepository;
use App\Repositories\Contracts\OrganizationRepository;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentUserRepository;


class RepositoryServiceProvider extends ServiceProvider
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
        $this->app->bind(PostRepository::class, EloquentPostRepository::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(EventRepository::class, EloquentEventRepository::class);
        $this->app->bind(OrganizationRepository::class, EloquentOrganizationRepository::class);
    }
}
