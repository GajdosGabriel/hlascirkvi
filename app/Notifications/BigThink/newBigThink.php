<?php

namespace App\Notifications\BigThink;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class newBigThink extends Notification
{
    use Queueable;

    protected $bigThink;

    public function __construct($bigThink)
    {
        $this->bigThing = $bigThink;
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
                    ->subject( 'Veľká myšlienka '. $this->bigThing->organization->title)
                    ->line( 'Napísal myšlienku '. $this->bigThing->body)
                    ->line('Viac informácií...')
                    ->action('Zobraziť na webe', url(route('post.show', [$this->bigThing->post_id, $this->bigThing->post->slug])) )
                    ->line('Svetu mier!');
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
