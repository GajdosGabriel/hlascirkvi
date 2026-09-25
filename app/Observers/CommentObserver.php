<?php

namespace App\Observers;

use App\Models\Comment;
use App\Notifications\Comments\InappropriateComment;
use App\Services\CommentModeration;
use App\Services\Youtube\CommentSync;

class CommentObserver
{
    public function saving(Comment $comment): void
    {
        if (! $comment->exists || $comment->isDirty('body') || $comment->isDirty('published')) {
            $reason = app(CommentModeration::class)->reason((string) $comment->body);
            $comment->moderation_reason = $reason;
            if ($reason) {
                $comment->published = null;
                $comment->reply_to_guest = false;
            }
        }
    }

    public function created(Comment $comment): void
    {
        $this->notifyAuthor($comment);
    }

    public function updated(Comment $comment): void
    {
        if ($comment->wasChanged(['body', 'moderation_reason'])) {
            $this->notifyAuthor($comment);
        }
    }

    private function notifyAuthor(Comment $comment): void
    {
        // YouTube neposkytuje e-mail autora; technickému účtu nepíšeme.
        if ($comment->moderation_reason && ! $comment->fromYoutube() && (int) $comment->user_id !== CommentSync::USER_ID) {
            $comment->user?->notify(new InappropriateComment($comment->body, $comment->moderation_reason));
        }
    }
}
