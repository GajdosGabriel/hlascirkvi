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
        $name = $user->first_name;
        $firstName = $this->findFirstName($name);

        if (! $firstName) {
            $name = $user->last_name;
            $firstName = $this->findFirstName($name);
        }

        if ($firstName) {
            // Slovenčina oslovuje nominatívom („Dobrý deň, Mária,"). Stĺpec
            // first_names.vocative má české tvary („Mário", „Petře"), preto sa
            // z tabuľky berie len to, že ide o krstné meno, a jeho rod —
            // oslovenie je meno tak, ako si ho užívateľ napísal.
            $user->vocative = $name;
            $user->gender = $firstName->gender;
        } else {
            $user->vocative = null;
        }
    }

    /**
     * `first_names` má kolaciu utf8mb4_unicode_ci, ktorá nerozlišuje diakritiku:
     * „Maria" tak vráti aj „Mária" a podľa počtu by vyhrala tá. Prednosť má
     * preto záznam, ktorý sedí na písmeno presne; variant s inou diakritikou sa
     * berie až vtedy, keď presný neexistuje (meno napísané bez dĺžňov, ktoré
     * v zozname bez nich nie je).
     */
    private function findFirstName(?string $name): ?FirstName
    {
        if ((string) $name === '') {
            return null;
        }

        $candidates = FirstName::whereName($name)->orderBy('count', 'desc')->get();

        return $candidates->firstWhere('name', $name) ?? $candidates->first();
    }

    public function creating(User $user)
    {
        $user->uuid ??= (string) Str::uuid7();
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
