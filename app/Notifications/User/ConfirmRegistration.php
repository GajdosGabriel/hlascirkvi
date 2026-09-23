<?php

namespace App\Notifications\User;

use App\Notifications\Messages\PortalMail;
use App\Models\PendingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Odkaz na dokončenie registrácie (App\Models\PendingRegistration). Kým naň
 * človek neklikne, účet neexistuje.
 */
class ConfirmRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $token  v čistej podobe — v databáze je len jeho hash
     * @param  bool  $reminder  automatická pripomienka (registrations:remind)
     */
    public function __construct(protected string $token, protected bool $reminder = false)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = PortalMail::for($notifiable);

        if ($this->reminder) {
            $mail->subject('Pripomienka: dokončite registráciu na HlasCirkvi.sk')
                ->line('pred pár dňami ste začali registráciu na portáli HlasCirkvi.sk, no ešte nie je dokončená. Účet vznikne až po kliknutí na tlačidlo nižšie.');
        } else {
            $mail->subject('Dokončite registráciu na HlasCirkvi.sk')
                ->line('niekto (dúfame, že vy) požiadal o vytvorenie účtu s touto e-mailovou adresou. Účet vznikne až po kliknutí na tlačidlo nižšie.');
        }

        $expires = $notifiable instanceof PendingRegistration ? $notifiable->expires_at : null;

        return $mail
            ->action('Potvrdiť a vytvoriť účet', route('register.confirm', $this->token))
            ->note(($expires ? 'Odkaz platí do '.$expires->format('d.m.Y').'.' : 'Odkaz platí '.PendingRegistration::TTL_DAYS.' dní.')
                .' Ak ste sa neregistrovali vy, e-mail pokojne ignorujte — bez potvrdenia účet nevznikne a údaje sa samy zmažú.'
                .($this->reminder ? ' Ďalšiu pripomienku už neposielame.' : ''));
    }
}
