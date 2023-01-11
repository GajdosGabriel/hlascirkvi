<?php

namespace App\Notifications\Subscribe;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSubscribe extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscribe;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($subscribe)
    {
        $this->subscribe = $subscribe;
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
            ->subject('Nová registrácia | ' . $this->subscribe->event->title)
            ->line('Nová registrácia.')
            ->line("Užívateľ: " . $this->subscribe->event->organization->user->fullname)
            ->line('sa prihlásil na akciu ' . $this->subscribe->event->title)
            ->action('Zobraziť prihlásených', url(route('event.subscribe.index', [$this->subscribe->event->id])))
            ->line('Správa bola generovaná systémom.');
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
