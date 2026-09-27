<?php

namespace App\Notifications\Canals;

use App\Models\Canal;
use App\Notifications\Messages\PortalMail;
use Illuminate\Notifications\Notification;

class CanalProfileCompleted extends Notification
{
    public function __construct(public Canal $canal, public array $changes) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): PortalMail
    {
        $labels = ['url_www' => 'Web', 'email' => 'E-mail', 'phone' => 'Telefón', 'street' => 'Ulica', 'description' => 'Popis'];
        $details = [];
        foreach ($this->changes as $field => $value) {
            $details[$labels[$field] ?? $field] = $value;
        }

        return PortalMail::for($notifiable)
            ->subject('Doplnili sme profil kanála '.$this->canal->title.' – Hlas Cirkvi')
            ->line('Aby návštevníci ľahšie našli vašu organizáciu, doplnili sme chýbajúce údaje kanála '.$this->canal->title.' z verejných zdrojov pomocou AI.')
            ->line('Vaše vyplnené údaje sme ponechali bez zmeny. Doplnili sme:')
            ->details($details)
            ->action('Skontrolovať profil', route('profile.canals.edit', $this->canal))
            ->line('Prosíme, skontrolujte správnosť doplnených údajov. Vo svojom profile ich môžete kedykoľvek upraviť.');
    }
}
