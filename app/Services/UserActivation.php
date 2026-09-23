<?php

namespace App\Services;

use App\Enums\CanalType;
use App\Models\Canal;
use App\Models\Prayer;
use App\Models\User;
use App\Notifications\Prayer\NewPrayer;
use App\Notifications\User\NewRegistration;
use App\Support\EmailMask;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

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
     * Účet pre adresu, ktorú autor práve preukázal kliknutím na odkaz
     * z čakárne (PendingPrayer, PendingComment). Do `users` sa zapisuje len
     * overený účet: nový vznikne rovno overený, starší neoverený sa overí.
     */
    public function verifiedUserFor(string $email): User
    {
        $user = User::whereEmail($email)->first();

        if (! $user) {
            $user = new User([
                // Meno sa zobrazuje verejne — nie celá časť e-mailu.
                'first_name' => EmailMask::name($email),
                'last_name' => '',
                'email' => $email,
            ]);
            // Overená adresa → UserObserver::created založí kanál a dá
            // vedieť administrátorom. Heslo si nastaví cez obnovu.
            $user->forceFill([
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ])->save();

            event(new Registered($user));
        } elseif (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            // Starší neoverený účet (spred čakárne) — Verified ho aktivuje.
            event(new Verified($user));
        }

        return $user;
    }

    /**
     * Zverejní modlitbu v kanáli užívateľa a dá vedieť administrátorom.
     *
     * @param  array<string, mixed>  $data
     */
    public function publishPrayer(User $user, array $data): Prayer
    {
        $prayer = $this->ensureCanal($user)->prayers()->create($data);

        Notification::send(User::role('admin')->get(), new NewPrayer($prayer));

        return $prayer;
    }

    /**
     * Aktívny kanál užívateľa; ak nespravuje žiadny, založí mu osobný.
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
