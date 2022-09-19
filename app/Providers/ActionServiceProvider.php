<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Actions\StorePost;
use App\Contracts\StorePostContract;

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
    }
}
