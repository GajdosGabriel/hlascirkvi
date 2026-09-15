<?php

namespace App\Notifications\Admin;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Administrátorom, keď automatické publikovanie (App\Services\Buffer) nemá
 * čo zverejniť.
 */
class BufeerIsEmpty extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return PortalMail::for($notifiable)
            ->subject('Zásobník videí je prázdny')
            ->line('automatické publikovanie nemá čo zverejniť — v zásobníku nie sú žiadne videá.')
            ->line('Kým sa zásobník nedoplní, na portáli nepribudnú nové príspevky.')
            ->action('Otvoriť zásobník', route('admin.buffer.index'));
    }
}
