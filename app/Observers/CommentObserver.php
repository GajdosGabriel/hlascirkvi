<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Comment;
use Notification;
use App\Notifications\Comments\CreatedNewComment;
use App\Notifications\Comments\UnpublishedComment;


class CommentObserver
{

    /**
     * Handle the comment "created" event.
     *
     * @param  \App\Models\Comment  $comment
     * @return void
     */
    public function created(Comment $comment)
    {
        if (!$comment->user_id == auth()->user()->id or !$comment->user_id == 100) {
            $comment->user->notify(new CreatedNewComment($comment));
        }

        // Pre Admins vytvorenie comments z neovereného účtu. 
        if ($comment->user->email_verified_at == NULL) {
            Notification::send(User::role('admin')->get(),  new UnpublishedComment($comment));
        }
    }

    /**
     * Handle the comment "updated" event.
     *
     * @param  \App\Models\Comment  $comment
     * @return void
     */
    public function updated(Comment $comment)
    {
        //
    }

    /**
     * Handle the comment "deleted" event.
     *
     * @param  \App\Models\Comment  $comment
     * @return void
     */
    public function deleted(Comment $comment)
    {
        //
    }

    /**
     * Handle the comment "restored" event.
     *
     * @param  \App\Models\Comment  $comment
     * @return void
     */
    public function restored(Comment $comment)
    {
        //
    }

    /**
     * Handle the comment "force deleted" event.
     *
     * @param  \App\Models\Comment  $comment
     * @return void
     */
    public function forceDeleted(Comment $comment)
    {
        //
    }
}
