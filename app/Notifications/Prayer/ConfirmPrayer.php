<?php

namespace App\Notifications\Prayer;

use App\Notifications\Messages\PortalMail;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Odkaz na zverejnenie modlitby pridanej bez prihlásenia
 * (App\Models\PendingPrayer). Kým naň autor neklikne, modlitba nie je verejná.
 */
class ConfirmPrayer extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $token  v čistej podobe — v databáze je len jeho hash
     * @param  bool  $reminder  automatická pripomienka (prayers:remind)
     */
    public function __construct(
        protected string $token,
        protected CarbonInterface $expiresAt,
        protected bool $reminder = false,
    ) {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = PortalMail::for($notifiable);

        if ($this->reminder) {
            $mail->subject('Vaša modlitba čaká na zverejnenie — potvrďte účet')
                ->line('pred pár dňami ste na portáli HlasCirkvi.sk pridali prosbu o modlitbu. Stále čaká na zverejnenie — stačí potvrdiť e-mailovú adresu tlačidlom nižšie.');
        } else {
            $mail->subject('Potvrďte zverejnenie modlitby na HlasCirkvi.sk')
                ->line('niekto (dúfame, že vy) pridal s touto e-mailovou adresou prosbu o modlitbu. Zverejní sa až po kliknutí na tlačidlo nižšie.');
        }

        return $mail
            ->quote($notifiable->body, $notifiable->title)
            ->action('Potvrdiť a zverejniť modlitbu', route('modlitby.confirm', $this->token))
            ->note('Odkaz platí do '.$this->expiresAt->format('d.m.Y').'. Potvrdením vás prihlásime do účtu na portáli — ak ho ešte nemáte, vznikne.'
                .' Ak ste modlitbu nepridali vy, e-mail pokojne ignorujte — bez potvrdenia sa nezverejní a údaje sa samy zmažú.'
                .($this->reminder ? ' Ďalšiu pripomienku už neposielame.' : ''));
    }
}
