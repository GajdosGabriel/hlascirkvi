<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Requests\SaveCommentsRequest;
use App\Notifications\Comments\CreatedNewComment;
use App\Repositories\Eloquent\EloquentUserRepository;

class CommentController extends Controller
{
    public function index()
    {
        // CommentResource siaha na commentable (slug, titulok) aj na autora.
        // Bez eager loadu si každý zo siedmich komentárov vypýtal vlastný
        // príspevok, jeho obrázky, kanál a užívateľa s rolami.
        $comments = Comment::with(['commentable', 'user'])
            ->latest()
            ->take(7)
            ->get();

        return CommentResource::collection($comments);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
    }

}
