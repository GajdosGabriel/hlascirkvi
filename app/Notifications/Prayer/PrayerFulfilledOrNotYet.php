<?php

namespace App\Notifications\Prayer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrayerFulfilledOrNotYet extends Notification
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
            ->subject('Predlžiť modlitbu? ' . $this->prayer->title)
            ->line('Je táto modlitba stále aktuálna alebo bola vypočut? ')
            ->greeting($this->prayer->title)
            ->line('Text modlitby:')
            ->line($this->prayer->body)
            ->line('Bola zverejnená: ' . $this->prayer->created_at)
            ->action('Modlitba je vypočutá' , $this->prayer->user->fullname)
            ->line('Ak modlitba bola vypočutá, zaradí sa do zoznamu vypočutých modlitieb. V prípade predlženia sa bude
            zobrazovať v zozname prosieb na vyslišanie.')
            ->action('Predľžiť modlitbu o 14 dní' , $this->prayer->user->fullname);

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
