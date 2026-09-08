<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Komentáre sa dali mazať a upravovať bez akejkoľvek kontroly — routy
 * `DELETE /api/comments/{comment}` a `PUT|DELETE /api/posts/{post}/comments/{comment}`
 * boli navyše aj mimo auth:sanctum.
 *
 * Vlastníkom je autor komentára; superadmin prejde cez Gate::before
 * (App\Providers\AuthServiceProvider).
 */
class CommentPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Comment $comment): bool
    {
        return $this->owns($user, $comment);
    }

    public function delete(User $user, Comment $comment): bool
    {
        // Zmazať komentár smie aj správca kanála, pod ktorý komentovaný
        // príspevok patrí — inak by nemal ako moderovať diskusiu.
        return $this->owns($user, $comment) || $this->managesCommentable($user, $comment);
    }

    protected function owns(User $user, Comment $comment): bool
    {
        return $comment->user_id !== null && $user->id === (int) $comment->user_id;
    }

    protected function managesCommentable(User $user, Comment $comment): bool
    {
        $commentable = $comment->commentable;

        if ($commentable === null || ! isset($commentable->organization_id)) {
            return false;
        }

        return $user->organizations()->whereKey($commentable->organization_id)->exists();
    }
}
