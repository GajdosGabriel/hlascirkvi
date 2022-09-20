<?php

namespace App\Providers;


use App\Actions\StorePost;
use App\Actions\AddUpdater;
use App\Actions\UpdatePost;
use App\Contracts\StorePostContract;
use App\Contracts\AddUpdaterContract;
use App\Contracts\UpdatePostContract;
use Illuminate\Support\ServiceProvider;

class ActionServiceProvider extends ServiceProvider
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
        $this->app->bind(StorePostContract::class, StorePost::class);
        $this->app->bind(UpdatePostContract::class, UpdatePost::class);
        $this->app->bind(AddUpdaterContract::class, AddUpdater::class);
    }
}
