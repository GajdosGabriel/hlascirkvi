<?php

namespace App\Observers;

use App\Models\Canal;

class CanalObserver
{
    /**
     * Handle the canal "created" event.
     *
     * @param  \App\Models\Canal  $canal
     * @return void
     */
    public function created(Canal $canal)
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
     * Handle the canal "updated" event.
     *
     * @param  \App\Models\Canal   $canal
     * @return void
     */
    public function updated(Canal $canal)
    {
        //
    }

    /**
     * Handle the canal "deleted" event.
     *
     * @param  \App\Models\Canal   $canal
     * @return void
     */
    public function deleted(Canal $canal)
    {
        //
    }

    /**
     * Handle the canal "restored" event.
     *
     * @param  \App\Models\Canal   $canal
     * @return void
     */
    public function restored(Canal $canal)
    {
        //
    }

    /**
     * Handle the canal "force deleted" event.
     *
     * @param  \App\Models\Canal   $canal
     * @return void
     */
    public function forceDeleted(Canal $canal)
    {
        //
    }
}
