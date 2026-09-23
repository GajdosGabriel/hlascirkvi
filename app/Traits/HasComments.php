<?php

namespace App\Traits;

use App\Models\Comment;

trait HasComments
{

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->with('user');
    }

    // addComment() so spoločným anonymným účtom (user_id 100) nahradilo
    // App\Services\PendingConfirmation::publishComment — komentár bez overenej
    // adresy čaká v App\Models\PendingComment.

}