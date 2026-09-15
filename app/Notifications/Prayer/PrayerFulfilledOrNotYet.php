<?php

namespace App\Notifications\Prayer;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Autorovi staršej modlitby s otázkou, či bola vypočutá
 * (App\Services\Prayers\UnansweredPrayers).
 */
class PrayerFulfilledOrNotYet extends Notification implements ShouldQueue
{
    use Queueable;

    protected $prayer;

    public function __construct($prayer)
    {
        $this->prayer = $prayer;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return PortalMail::for($notifiable)
            ->level('success')
            ->subject('Bola vaša modlitba vypočutá?')
            ->line('pred časom ste na HlasCirkvi.sk zverejnili tento modlitebný úmysel:')
            ->quote($this->prayer->body, $this->prayer->title)
            ->line('Ak bola modlitba vypočutá, dajte nám vedieť. Zaradíme ju medzi vypočuté — môže povzbudiť aj ostatných.')
            // Podpísaná URL — inak by stačilo uhádnuť ID a označiť cudziu
            // modlitbu za vypočutú.
            ->action('Áno, modlitba bola vypočutá', URL::signedRoute('prayer.fulfilledAt', ['prayer' => $this->prayer->id]))
            ->note('Ak ešte vypočutá nebola, netreba nič robiť — úmysel ostáva medzi aktuálnymi.');
    }
}
