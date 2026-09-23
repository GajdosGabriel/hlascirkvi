<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\UserActivation;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;

/**
 * Účet založený bez overenej adresy (komentár, modlitba či obľúbené bez
 * registrácie) dostane kanál až vtedy, keď sa adresa potvrdí.
 */
class ActivateVerifiedUser
{
    public function __construct(protected UserActivation $activation)
    {
    }

    public function verified(Verified $event): void
    {
        if ($event->user instanceof User) {
            $this->activation->activate($event->user);
        }
    }

    /**
     * Odkaz na obnovu hesla prišiel do schránky, takže adresa je tým tiež
     * preukázaná — netreba chcieť ešte druhý potvrdzovací e-mail.
     */
    public function passwordReset(PasswordReset $event): void
    {
        $user = $event->user;

        if ($user instanceof User && ! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Verified::class => 'verified',
            PasswordReset::class => 'passwordReset',
        ];
    }
}
