<?php

namespace App\Notifications\Prayer;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewPrayer extends Notification implements ShouldQueue
{
    use Queueable;

    protected $prayer;

    public function __construct($prayer)
    {
        $this->prayer = $prayer;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return PortalMail::for($notifiable)
            ->subject('Nová modlitba: '.$this->prayer->title)
            ->line('na portáli pribudol nový modlitebný úmysel:')
            ->quote($this->prayer->body, $this->prayer->title)
            ->details([
                'Autor' => $this->prayer->user?->adminName(),
                'Pridaná' => $this->prayer->created_at?->format('d.m.Y H:i'),
            ])
            ->action('Zobraziť v administrácii', route('admin.prayer.index'));
    }

    public function toArray($notifiable)
    {
        return [
            'logo' =>  $this->prayer->user->owner->initialName,
            'message' => $this->prayer->user->fullname . ' pridal modlitbu ' . $this->prayer->title,
            'link' => route('modlitby.index')
        ];
    }
}
