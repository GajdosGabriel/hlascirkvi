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
        /*
         * Novému kanálu sa tu nastavovalo `updaters()->sync([1])`, teda
         * zaradenie „živé vysielanie" — jeho videá by sa po importe
         * zverejňovali okamžite namiesto toho, aby prešli bufferom. Pri kanáli
         * založenom cez formulár to nič nerobilo (CanalRequest::save hneď
         * potom synchronizoval updatery znova a zaradenie prepísal), inde
         * zostalo. Smerovanie nových videí dnes nesie `post_section`
         * s predvolenou hodnotou `front`, takže netreba nastavovať nič.
         */
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
