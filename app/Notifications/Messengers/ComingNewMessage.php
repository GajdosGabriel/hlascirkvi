<?php

namespace App\Notifications\Messengers;

use App\Messenger;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ComingNewMessage extends Notification
{
    use Queueable;


    protected $message;
    public function __construct($message)
    {
        $this->message = $message;
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
            ->subject( $this->message->user->fullName . ', má novú správu')
            ->greeting('Dobrý deň,')
            ->line($this->message->person->full_name . ' Vám posiela túto správu: ')
            ->line($this->message->body)
            ->line('Správa bola zaslaná prostretníctvom CirkevOnline.sk - Kresťanský portál.')
            ->action('Odpovedať na správu', url('/'))
            ->line('Ďakujeme že na otázku skoro odpoviete!');
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
            'message' => $this->message->person->fullName . ' Vám poslal správu ',
            'link' => 'url link'
            //            'link' => $this->post->path()
        ];
    }
}
