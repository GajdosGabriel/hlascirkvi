<?php

namespace App\Notifications\Prayer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPrayer extends Notification
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
        return ['mail', 'database'];
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
            ->subject('Modlitba ' . $this->prayer->title)
            ->line('Pridaná nová modlitba: ')
            ->line($this->prayer->body)
            ->line('Dátum: ' . $this->prayer->created_at)
            ->line('User: ' . $this->prayer->user->fullname);
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
            'message' => $this->prayer->user->fullName . ' Pridal modlitbu ' . $this->prayer->title,
            'link' => route('modlitby.index')
        ];
    }
}
