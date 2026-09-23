<?php

namespace App\Notifications\Comments;

use App\Notifications\Messages\PortalMail;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Odkaz na zverejnenie komentára, ktorého autor nemá overenú adresu
 * (App\Models\PendingComment). Kým naň neklikne, komentár nie je verejný.
 */
class ConfirmComment extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $token  v čistej podobe — v databáze je len jeho hash
     * @param  bool  $reminder  automatická pripomienka (pending:remind)
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
        $title = $notifiable->post?->title;

        if ($this->reminder) {
            $mail->subject('Váš komentár čaká na zverejnenie — potvrďte účet')
                ->line('pred pár dňami ste na portáli HlasCirkvi.sk napísali komentár. Stále čaká na zverejnenie — stačí potvrdiť e-mailovú adresu tlačidlom nižšie.');
        } else {
            $mail->subject('Potvrďte zverejnenie komentára na HlasCirkvi.sk')
                ->line('niekto (dúfame, že vy) napísal s touto e-mailovou adresou komentár. Zverejní sa až po kliknutí na tlačidlo nižšie.');
        }

        return $mail
            ->quote($notifiable->body, $title ? 'Komentár k príspevku „'.$title.'"' : null)
            ->action('Potvrdiť a zverejniť komentár', route('comments.confirm', $this->token))
            ->note('Odkaz platí do '.$this->expiresAt->format('d.m.Y').'. Potvrdením vás prihlásime do účtu na portáli — ak ho ešte nemáte, vznikne.'
                .' Ak ste komentár nenapísali vy, e-mail pokojne ignorujte — bez potvrdenia sa nezverejní a údaje sa samy zmažú.'
                .($this->reminder ? ' Ďalšiu pripomienku už neposielame.' : ''));
    }
}
