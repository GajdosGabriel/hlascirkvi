<?php

namespace App\Notifications\Canals;

use App\Models\Canal;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Správa návštevníka pre kanál z jeho verejnej stránky. E-mail kanála sa na
 * stránke neukazuje, správa naň odchádza odtiaľto a odpoveď ide cez Reply-To
 * priamo odosielateľovi.
 */
class CanalMessage extends Notification
{
    use Queueable;

    public function __construct(
        protected Canal $canal,
        protected User $sender,
        protected string $body,
    ) {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nová správa pre ' . $this->canal->title . ' – HlasCirkvi.sk')
            ->replyTo($this->sender->email, $this->sender->fullname)
            ->greeting('Dobrý deň,')
            ->line($this->sender->fullname . ' Vám cez stránku kanála posiela túto správu:')
            ->line($this->body)
            ->line('Odpovedať môžete priamo na tento e-mail, odpoveď dostane odosielateľ.')
            ->salutation('HlasCirkvi.sk – Kresťanský portál');
    }
}
