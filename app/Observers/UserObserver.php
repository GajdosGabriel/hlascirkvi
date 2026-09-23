<?php

namespace App\Observers;

use App\Models\User;
use App\Models\FirstName;
use App\Services\UserActivation;
use Illuminate\Support\Str;

class UserObserver
{

    /**
     * Beží pri každom uložení užívateľa, aj pri zmene jediného stĺpca. Preto
     * sa slug a oslovenie prepočítavajú len vtedy, keď sa naozaj zmenilo meno
     * — dovtedy to boli dva dopyty na `first_names` pri každom $user->update(),
     * napríklad pri kliknutí na zvonček.
     */
    public function saving(User $user)
    {
        if (! $user->isDirty(['first_name', 'last_name'])) {
            return;
        }

        $user->slug =  Str::slug($user->first_name . " " . $user->last_name, '-');

        // Ak zamenia first name s last name
        $firstName = FirstName::whereName($user->first_name)->orderBy('count', 'desc')->first();

        if(!$firstName){
            $firstName = FirstName::whereName($user->last_name)->orderBy('count', 'desc')->first();
        }

        if ($firstName) {
            $user->vocative = $firstName->vocative;
            $user->gender = $firstName->gender;
        } else {
            $user->vocative = null;
        }
    }

    /**
     * Stĺpec `api_token` sa nikde nečíta — v kóde je toto jediný zápis a žiadny
     * guard ho nepoužíva. Pretáčal sa pritom pri každom uložení užívateľa.
     * Keďže je v schéme NOT NULL bez defaultu, generuje sa aspoň raz pri
     * založení účtu; zrušiť ho môže až migrácia.
     */
    public function creating(User $user)
    {
        $user->uuid ??= (string) Str::uuid7();
        $user->api_token = bin2hex(openssl_random_pseudo_bytes(30));
    }

    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        $user->assignRole('user');

        // Kanál a správa adminom len pre overenú adresu. Neoverený účet
        // (komentár bez registrácie) sa aktivuje až pri udalosti Verified —
        // App\Listeners\ActivateVerifiedUser.
        if ($user->hasVerifiedEmail()) {
            app(UserActivation::class)->activate($user);
        }
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
