<?php

namespace App\Notifications\User;

use App\Notifications\Messages\PortalMail;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Obnova hesla. User nemal vlastnú sendPasswordResetNotification(), takže
 * ľuďom chodil Laravelov anglický e-mail („Reset Password Notification").
 * Odkaz aj token rieši rodič, tu je len slovenský text v šablóne portálu.
 */
class ResetPassword extends BaseResetPassword implements ShouldQueue
{
    use Queueable;

    public function toMail($notifiable)
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return PortalMail::for($notifiable)
            ->subject('Obnova hesla')
            ->line('dostali sme žiadosť o obnovenie hesla k vášmu účtu na HlasCirkvi.sk.')
            ->action('Nastaviť nové heslo', $this->resetUrl($notifiable))
            ->note("Odkaz platí {$minutes} minút. Ak ste o obnovu nežiadali, e-mail ignorujte — vaše heslo ostáva nezmenené.");
    }
}
