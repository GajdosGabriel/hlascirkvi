<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Http\Controllers\Controller;

/**
 * „Páči sa mi to“ pri komentári. Vracia skutočný stav a počet zo servera,
 * aby sa tlačidlo po optimistickom prepnutí zosúladilo s databázou.
 */
class CommentLikeController extends Controller
{
    public function store(Comment $comment)
    {
        $liked = $comment->toggleFavorite();

        return [
            'is_favorited' => $liked,
            'favorites_count' => $comment->favorites()->count(),
        ];
    }
}
