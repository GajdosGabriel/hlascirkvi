<?php

namespace App\Notifications\Comments;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Autorovi komentára do zvončeka, keď mu niekto odpovie.
 */
class RepliedToComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reply;

    public function __construct($reply)
    {
        $this->reply = $reply;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $commentable = $this->reply->commentable;
        $user = $this->reply->user;
        $name = trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? ''));

        return [
            'message' => ($name !== '' ? $name : 'Niekto')
                . ' odpovedal na váš komentár pri ' . ($commentable?->title ?? 'príspevku'),
            'link' => $commentable?->path(),
        ];
    }
}
