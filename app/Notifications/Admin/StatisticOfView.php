<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class StatisticOfView extends Notification
{
    use Queueable;

    public $model;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
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
        // Zisti či je to Post alebo event
        if(class_basename($this->model) == 'Post')  $path = route('post.show' , [$this->model->id, $this->model->slug]);

        if(class_basename($this->model) == 'Event') $path = route('akcie.show' , [$this->model->id, $this->model->slug]);


        return (new MailMessage)
            ->subject( 'Váš príspevok '. $this->model->title)
            ->greeting('Dobrý deň,')
            ->line('Na kresťanskom portály HlasCirkvi.sk ')
            ->line('Váš príspevok '. $this->model->title . ' videlo: ' . $this->model->count_view . ' návštevníkov.')
            ->action('Zobraziť článok', $path)
            ->line('Ďakujeme že píšete skvelé príspevky!');
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
