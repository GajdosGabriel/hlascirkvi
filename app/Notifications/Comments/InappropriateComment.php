<?php

namespace App\Notifications\Comments;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InappropriateComment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $body, public string $reason)
    {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return PortalMail::for($notifiable)
            ->subject('Váš komentár bol skrytý')
            ->line('Automatická kontrola označila váš komentár ako nevhodný: ' . $this->reason . '.')
            ->line('Komentár nie je verejne zobrazený. Prosíme, vyjadrite svoj názor bez urážok a vyhrážok. Ak ide o omyl, kontaktujte správcu portálu.')
            ->quote($this->body);
    }
}
