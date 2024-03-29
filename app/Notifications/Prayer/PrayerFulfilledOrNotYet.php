<?php

namespace App\Notifications\Prayer;

use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PrayerFulfilledOrNotYet extends Notification implements ShouldQueue
{
    use Queueable;

    protected $prayer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($prayer)
    {
        $this->prayer = $prayer;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Vaša modlitba, ' . $this->prayer->title)
            ->greeting($this->prayer->title)
            ->line($this->prayer->body)
            // ->line('Zverejnená: ' . $this->prayer->created_at)
            ->line('Ak modlitba bola vypočutá, kliknutím na tlačidlo, ju zaradíte zoznamu vypočutých modlitieb.')
            ->action('Modlitba bola vypočutá' , route('prayer.fulfilledAt', $this->prayer->id))
            ->line('V opačnom prípade nereagujte a modlitebný úmysle bude stále aktuálny.')
            // ->line(new HtmlString('<a href="/" style="display:block; margin: 0 auto; width: 180px;">Modlitba bola vypočutá</a>'))
            ->salutation('S pozdravom');

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
