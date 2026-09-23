<?php

namespace App\Services;

use App\Enums\CanalType;
use App\Models\Canal;
use App\Models\User;
use App\Notifications\User\NewRegistration;
use Illuminate\Support\Facades\Notification;

/**
 * Z účtu sa stáva „skutočný" užívateľ: dostane vlastný kanál a administrátori
 * správu o novej registrácii.
 *
 * Doteraz sa to dialo v UserObserver::created pri každom novom riadku
 * v `users`, takže každá vymyslená registrácia mala hneď aj verejný kanál
 * a v pošte adminov pribudol ďalší e-mail. Teraz až keď je adresa overená:
 *  - Google/Facebook a potvrdená registrácia z formulára vznikajú už overené
 *    (UserObserver::created),
 *  - účty založené komentárom či modlitbou bez registrácie až po kliknutí
 *    na odkaz z e-mailu alebo po obnove hesla (udalosť Verified).
 */
class UserActivation
{
    public function activate(User $user): void
    {
        // Už aktivovaný (alebo kanál dostal skôr, napr. pri modlitbe).
        if ($user->canals()->exists()) {
            return;
        }

        $this->ensureCanal($user);

        Notification::send(User::role('admin')->get(), new NewRegistration($user));
    }

    /**
     * Aktívny kanál užívateľa; ak nespravuje žiadny, založí mu osobný.
     * Modlitba bez registrácie ho potrebuje hneď, nie až po overení.
     */
    public function ensureCanal(User $user): Canal
    {
        if ($user->canal_id !== null && $canal = $user->canal()->first()) {
            return $canal;
        }

        if ($canal = $user->canals()->first()) {
            $user->update(['canal_id' => $canal->id]);

            return $canal;
        }

        // Menovci dostávali kanál s rovnakým názvom (unique platí len vo
        // formulári), a ten potom nešiel uložiť. Emoji v mene sa do názvu
        // kanála neprenesú.
        $canal = $user->canals()->create([
            'title' => Canal::uniqueTitle((string) $user->fullname),
            'slug' => $user->slug,
            'type' => CanalType::Personal,
            'village_id' => 4209,
        ]);

        $user->update(['canal_id' => $canal->id]);

        return $canal;
    }
}
