<?php

namespace App\Notifications\User;

use App\Models\User;
use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ConfirmEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return PortalMail::for($notifiable)
            ->subject('Potvrďte svoju e-mailovú adresu')
            ->line('na portáli HlasCirkvi.sk vznikol účet s touto e-mailovou adresou. Stačí ju jedným kliknutím potvrdiť.')
            ->line('Potvrdením získate plný prístup — komentáre, obľúbené príspevky aj odber noviniek.')
            ->action('Potvrdiť e-mailovú adresu', $this->verificationUrl())
            ->note('Odkaz platí 7 dní. Ak ste sa neregistrovali vy, e-mail pokojne ignorujte — bez potvrdenia sa s adresou nič nedeje.');
    }

    /**
     * Podpísaná adresa s obmedzenou platnosťou. V ceste je aj odtlačok
     * e-mailu, takže po zmene adresy staré odkazy prestanú platiť — inak by
     * sa dal overiť e-mail, ktorý účtu už nepatrí.
     */
    protected function verificationUrl(): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addDays(7), [
            'user' => $this->user->getKey(),
            'hash' => sha1($this->user->getEmailForVerification()),
        ]);
    }
}
