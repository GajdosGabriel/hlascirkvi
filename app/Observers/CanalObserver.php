<?php

namespace App\Observers;

use App\Models\Canal;

class CanalObserver
{
    /**
     * Handle the organization "created" event.
     *
     * @param  \App\Models\Canal  $organization
     * @return void
     */
    public function created(Canal $organization)
    {
        if (auth()->check()) {
            $organization->updaters()->sync([1]); // nastaví iba používateľa s ID 5
        }
    }

    /**
     * Handle the organization "updated" event.
     *
     * @param  \App\Models\Canal   $organization
     * @return void
     */
    public function updated(Canal $organization)
    {
        //
    }

    /**
     * Handle the organization "deleted" event.
     *
     * @param  \App\Models\Canal   $organization
     * @return void
     */
    public function deleted(Canal $organization)
    {
        //
    }

    /**
     * Handle the organization "restored" event.
     *
     * @param  \App\Models\Canal   $organization
     * @return void
     */
    public function restored(Canal $organization)
    {
        //
    }

    /**
     * Handle the organization "force deleted" event.
     *
     * @param  \App\Models\Canal   $organization
     * @return void
     */
    public function forceDeleted(Canal $organization)
    {
        //
    }
}
