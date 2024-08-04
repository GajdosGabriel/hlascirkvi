<?php

namespace App\Traits;

use App\Models\Comment;

trait HasComments
{

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->with('organization');
    }

    public function addComment($comment)
    {
        if (auth()->check()) {
            $comment = $this->comments()->create(array_merge($comment, ['organization_id' => auth()->user()->org_id]));
            return $comment;
        }
        // organization_id 100 in unknowle user for anonyms comments
        $comment = $this->comments()->create(array_merge($comment, ['organization_id' => 100]));
        $comment->delete();

        return $comment;
    }
    
}