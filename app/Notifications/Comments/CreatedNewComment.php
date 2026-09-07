<?php

namespace App\Notifications\Comments;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CreatedNewComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Len zvonček v aplikácii. Mailová vetva bola dodnes nedosiahnuteľná
        // (podmienka v PostCommentController sa nikdy nevyhodnotila správne)
        // a jej obsah zostal na stubu z `make:notification`. Kým nemá text,
        // nemá čo chodiť ľuďom do schránky.
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
        $commentable = $this->comment->commentable;

        return (new MailMessage)
                    ->subject('Nový komentár')
                    ->line('Pri vašom príspevku „' . ($commentable?->title ?? '') . '" pribudol nový komentár.')
                    ->action('Zobraziť príspevok', url($commentable?->path() ?? '/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // Comment nemá stĺpec organization_id, takže $comment->organization
        // bolo vždy null a notifikácia padala na ->title. Kanál je až na
        // komentovanom príspevku.
        $commentable = $this->comment->commentable;

        return [
            'message' => ($commentable?->organization?->title ?? 'Niekto')
                . ' komentoval ' . ($commentable?->title ?? 'príspevok'),
            'link' => $commentable?->path()
        ];
    }
}
