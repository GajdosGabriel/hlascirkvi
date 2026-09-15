<?php

namespace App\Notifications\Messengers;

use App\Models\Messenger;
use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Správa od iného používateľa (MessengerObserver::created).
 *
 * Tlačidlo „Odpovedať na správu" viedlo na titulku, kde sa odpovedať nedalo.
 * Odpoveď teraz ide cez Reply-To priamo odosielateľovi. Mailom-only
 * notifikácia mala aj toArray() s neexistujúcim $this->post — odstránené.
 */
class ComingNewMessage extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct(Messenger $message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $sender = $this->message->senderUser;

        return PortalMail::for($notifiable)
            ->subject('Nová správa od '.$sender->fullname)
            ->replyTo($sender->email, $sender->fullname)
            ->line($sender->fullname.' vám cez portál HlasCirkvi.sk posiela správu:')
            ->quote($this->message->body)
            ->line('Odpovedať môžete priamo na tento e-mail, odpoveď dostane odosielateľ.')
            ->note('Kým neodpoviete, vaša e-mailová adresa ostáva odosielateľovi skrytá.');
    }
}
