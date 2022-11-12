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
        return  CommentResource::collection(Comment::latest()->take(7)->get());
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
    }

}
