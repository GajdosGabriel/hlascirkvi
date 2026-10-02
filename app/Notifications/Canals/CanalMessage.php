<?php

namespace App\Notifications\Canals;

use App\Models\Canal;
use App\Models\User;
use Illuminate\Bus\Queueable;
use App\Notifications\Messages\PortalMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Správa návštevníka pre kanál z jeho verejnej stránky. E-mail kanála sa na
 * stránke neukazuje, správa naň odchádza odtiaľto a odpoveď ide cez Reply-To
 * priamo odosielateľovi.
 */
class CanalMessage extends Notification implements ShouldQueue
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
        // Text správy je od cudzieho človeka: quote() ho escapuje a uzavrie do
        // citačného bloku, takže markdown odkazy sa nevykreslia ako odkazy portálu.
        return PortalMail::for($notifiable)
            ->subject('Nová správa pre ' . $this->canal->title . ' – HlasCirkvi.sk')
            ->replyTo($this->sender->email, $this->sender->fullname)
            ->line(e($this->sender->fullname) . ' Vám cez stránku kanála posiela túto správu:')
            ->quote($this->body)
            ->note('Správu napísal návštevník portálu, nie portál. Odkazy v nej neotvárajte, ak odosielateľa nepoznáte.'
                . ' Odpovedať môžete priamo na tento e-mail — odpoveď dostane odosielateľ a uvidí tak vašu e-mailovú adresu.');
    }
}
