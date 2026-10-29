<?php

namespace App\Notifications\Support;

use App\Models\SupportMessage;
use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Nová správa vo vlákne podpory.
 *
 * Smer určuje text aj odkaz: používateľ dostane „podpora odpovedala" s odkazom
 * do nástenky, správca „používateľ doplnil" s odkazom do administrácie.
 * Kto napísal, ten upozornenie nedostane.
 */
class SupportTicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SupportMessage $message)
    {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = $this->message->loadMissing(['author', 'ticket']);
        // Používateľ vidí podporu ako „Podpora HlasCirkvi.sk", nie konkrétneho správcu.
        $author = $message->is_staff ? 'Podpora HlasCirkvi.sk' : ($message->author?->adminName() ?? 'Používateľ');

        return PortalMail::for($notifiable)
            ->subject($this->subject())
            ->line($author.' napísal(a) vo vlákne '.$message->ticket->reference().':')
            ->quote($message->body, $message->ticket->subject)
            ->action($message->is_staff ? 'Zobraziť a odpovedať' : 'Odpovedať v administrácii', $this->url())
            ->note('Odpovedajte prosím cez odkaz vyššie, nie na tento e-mail — odpoveď tak ostane vo vlákne.');
    }

    public function toArray($notifiable): array
    {
        return [
            'logo' => 'P',
            'message' => $this->subject(),
            'link' => $this->url(),
            'ticket_id' => $this->message->support_ticket_id,
        ];
    }

    private function subject(): string
    {
        $ticket = $this->message->loadMissing('ticket')->ticket;

        return ($this->message->is_staff ? 'Podpora odpovedala ' : 'Nová správa vo vlákne ')
            .$ticket->reference().': '.$ticket->subject;
    }

    private function url(): string
    {
        return $this->message->is_staff
            ? route('profile.support.show', $this->message->support_ticket_id)
            : route('admin.support.show', $this->message->support_ticket_id);
    }
}
