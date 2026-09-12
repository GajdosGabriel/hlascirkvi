<?php

namespace App\Notifications\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Potvrďte svoju e-mailovú adresu na HlasCirkvi.sk')
            ->greeting('Dobrý deň,')
            ->line('na portáli HlasCirkvi.sk vznikol účet s touto e-mailovou adresou.')
            ->line('Potvrdením získate plný prístup — komentáre, obľúbené príspevky aj odber noviniek.')
            ->action('Potvrdiť e-mailovú adresu', $this->verificationUrl())
            ->line('Odkaz platí 7 dní. Ak ste sa neregistrovali vy, tento e-mail pokojne ignorujte — bez potvrdenia sa s adresou nič nedeje.');
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
