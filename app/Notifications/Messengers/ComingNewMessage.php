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
    public function __construct(Messenger $message)
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
            ->subject( 'Nová správa - spolupráca/')
            ->greeting('Dobrý deň,')
            ->line($this->message->person->fullname . ' Vám posiela túto správu: ')
            ->line($this->message->body)
            ->line('Správa bola zaslaná prostretníctvom HlasCirkvi.sk - Kresťanský portál.')
            ->action('Odpovedať na správu', url('/'))
            ->line('Ďakujeme za skorú odpoveď!');
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
            'message' => $this->message->person->fullname . ' Vám poslal správu ',
//            'link' => 'url link'
                        'link' => $this->post->path()
        ];
    }
}
