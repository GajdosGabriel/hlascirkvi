<?php

namespace App\Observers;

use App\User;
use App\Comment;
use Notification;
use App\Notifications\Comments\CreatedNewComment;
use App\Notifications\Comments\UnpublishedComment;


class CommentObserver
{

    /**
     * Handle the comment "created" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function created(Comment $comment)
    {
        if(! $comment->user_id == auth()->id())
        {
            $comment->user->notify(new CreatedNewComment($comment));
        }

        if($comment->active == 0) {
            Notification::send(User::role('admin')->get(),  new UnpublishedComment($comment));
        }

    }

    /**
     * Handle the comment "updated" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function updated(Comment $comment)
    {
        //
    }

    /**
     * Handle the comment "deleted" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function deleted(Comment $comment)
    {
        //
    }

    /**
     * Handle the comment "restored" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function restored(Comment $comment)
    {
        //
    }

    /**
     * Handle the comment "force deleted" event.
     *
     * @param  \App\Comment  $comment
     * @return void
     */
    public function forceDeleted(Comment $comment)
    {
        //
    }
}
