<?php

namespace App\Notifications\User;

use App\Models\Canal;
use App\Models\Prayer;
use App\Notifications\Messages\PortalMail;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Odkaz na potvrdenie „Pripojiť sa k modlitbe" / odberu kanála od
 * neprihláseného (App\Models\PendingFavorite). Kým naň neklikne, nič sa
 * nezapočíta.
 */
class ConfirmFavorite extends Notification implements ShouldQueue
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
        $model = $notifiable->favorited;

        [$what, $action] = match (true) {
            $model instanceof Prayer => ['pripojenie k modlitbe', 'Potvrdiť a pripojiť sa k modlitbe'],
            $model instanceof Canal => ['odber kanála '.$model->title, 'Potvrdiť odber kanála'],
            default => ['označenie na portáli', 'Potvrdiť'],
        };

        $mail = PortalMail::for($notifiable);

        if ($this->reminder) {
            $mail->subject('Vaše '.$what.' čaká na potvrdenie')
                ->line('pred pár dňami ste na portáli HlasCirkvi.sk požiadali o '.$what.'. Stále čaká — stačí potvrdiť e-mailovú adresu tlačidlom nižšie.');
        } else {
            $mail->subject('Potvrďte '.$what.' na HlasCirkvi.sk')
                ->line('niekto (dúfame, že vy) požiadal s touto e-mailovou adresou o '.$what.'. Započíta sa až po kliknutí na tlačidlo nižšie.');
        }

        if ($model instanceof Prayer) {
            $mail->quote($model->body, $model->title);
        }

        return $mail
            ->action($action, route('favorites.confirm', $this->token))
            ->note('Odkaz platí do '.$this->expiresAt->format('d.m.Y').'. Potvrdením vás prihlásime do účtu na portáli — ak ho ešte nemáte, vznikne.'
                .' Ak ste o to nežiadali vy, e-mail pokojne ignorujte — bez potvrdenia sa nič nestane a údaje sa samy zmažú.'
                .($this->reminder ? ' Ďalšiu pripomienku už neposielame.' : ''));
    }
}
