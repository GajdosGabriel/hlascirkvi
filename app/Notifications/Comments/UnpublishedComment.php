<?php

namespace App\Notifications\Comments;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Administrátorom o komentári z neovereného účtu. Odoslanie je zatiaľ
 * zakomentované v CommentObserver.
 */
class UnpublishedComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $commentable = $this->comment->commentable;

        return PortalMail::for($notifiable)
            ->subject('Komentár čaká na schválenie')
            ->line('používateľ s neovereným e-mailom pridal komentár, ktorý čaká na schválenie.')
            ->quote($this->comment->body, $this->comment->user?->fullname)
            ->details([
                'Príspevok' => $commentable?->title,
                'Pridaný' => $this->comment->created_at?->format('d.m.Y H:i'),
            ])
            ->action('Otvoriť komentáre', route('admin.comment.index'));
    }
}
