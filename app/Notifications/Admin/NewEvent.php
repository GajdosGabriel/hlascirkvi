<?php

namespace App\Notifications\Admin;

use Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewEvent extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Event ' . $this->event->title)
            ->line('Podujatie čaká na schválenie. ' . $this->event->title)
            ->action('Upraviť event', url(route('profile.event.edit', [$this->event->organization_id, $this->event->id]) ))
            ->line('Dátum: ' . $this->event->start_at)
            ->line('Miesto: ' . $this->event->village->fullname)
            ->line('Publikovaný: ' . $this->event->published)
            ->line('Organizácia: ' . $this->event->organization->title)
            ->line('Názov: ' . $this->event->body);

    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
