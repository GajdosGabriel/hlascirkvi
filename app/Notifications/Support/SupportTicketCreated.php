<?php

namespace App\Notifications\Support;

use App\Models\SupportTicket;
use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/** „Používateľ napísal podpore." Ide správcom — e-mailom aj do zvončeka. */
class SupportTicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SupportTicket $ticket)
    {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // ShouldQueue: na workeri je model čerstvo načítaný bez relácií.
        $ticket = $this->ticket->loadMissing('user');
        $user = $ticket->user;

        $mail = PortalMail::for($notifiable)
            ->subject($this->subject())
            ->line(($user?->adminName() ?? 'Používateľ').' napísal podpore portálu:')
            ->quote($ticket->messages()->first()?->body, $ticket->subject)
            ->details([
                'Téma' => $ticket->category->label(),
                'E-mail' => $user?->email,
                'Stránka' => $ticket->page_url,
            ])
            ->action('Odpovedať v administrácii', route('admin.support.show', $ticket));

        // Odpoveď e-mailom by sa vo vlákne nezaevidovala.
        return $mail->note('Odpovedajte prosím v administrácii, nie na tento e-mail — odpoveď tak ostane vo vlákne.');
    }

    public function toArray($notifiable): array
    {
        return [
            'logo' => 'P',
            'message' => $this->subject(),
            'link' => route('admin.support.show', $this->ticket),
            'ticket_id' => $this->ticket->id,
        ];
    }

    private function subject(): string
    {
        return 'Podpora '.$this->ticket->reference().': '.$this->ticket->subject;
    }
}
