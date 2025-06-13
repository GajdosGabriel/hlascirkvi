<?php

namespace App\Observers;

use App\Models\Organization;

class OrganizationObserver
{
    /**
     * Handle the organization "created" event.
     *
     * @param  \App\Models\Organization  $organization
     * @return void
     */
    public function created(Organization $organization)
    {
        if (auth()->check()) {
            $organization->updaters()->sync([1]); // nastaví iba používateľa s ID 5
        }
    }

    /**
     * Handle the organization "updated" event.
     *
     * @param  \App\Models\Organization   $organization
     * @return void
     */
    public function updated(Organization $organization)
    {
        //
    }

    /**
     * Handle the organization "deleted" event.
     *
     * @param  \App\Models\Organization   $organization
     * @return void
     */
    public function deleted(Organization $organization)
    {
        //
    }

    /**
     * Handle the organization "restored" event.
     *
     * @param  \App\Models\Organization   $organization
     * @return void
     */
    public function restored(Organization $organization)
    {
        //
    }

    /**
     * Handle the organization "force deleted" event.
     *
     * @param  \App\Models\Organization   $organization
     * @return void
     */
    public function forceDeleted(Organization $organization)
    {
        //
    }
}
