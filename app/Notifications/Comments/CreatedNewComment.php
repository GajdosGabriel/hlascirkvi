<?php

namespace App\Notifications\Comments;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CreatedNewComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        // Zatiaľ len zvonček v aplikácii. Mailová podoba je pripravená,
        // stačí pridať 'mail' — ideálne až s nastavením odberu pri užívateľovi.
        return ['database'];
    }

    public function toMail($notifiable)
    {
        $commentable = $this->comment->commentable;

        return PortalMail::for($notifiable)
            ->subject('Nový komentár pri príspevku '.($commentable?->title ?? ''))
            ->line('pri vašom príspevku „'.($commentable?->title ?? '').'" pribudol nový komentár:')
            ->quote($this->comment->body, $this->comment->user?->fullname)
            ->action('Zobraziť príspevok', url($commentable?->path() ?? '/'));
    }

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
