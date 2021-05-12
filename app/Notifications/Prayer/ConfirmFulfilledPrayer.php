<?php

namespace App\Notifications\Prayer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmFulfilledPrayer extends Notification
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
        return ['database'];
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
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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
            'logo' =>  $this->prayer->user->owner->initialName,
            'message' => $this->prayer->user->fullName . ' Potvrdil vypočutú modlitbu ' . $this->prayer->title,
            'link' => route('modlitby.index')
        ];
    }
}
