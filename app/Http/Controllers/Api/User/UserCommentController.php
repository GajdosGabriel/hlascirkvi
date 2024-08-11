<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;


class UserCommentController extends Controller
{
    public function index($user)
    {
        return  CommentResource::collection(Comment::whereUserId(auth()->user()->id)->latest()->take(7)->get());
    }

    // public function destroy(Comment $comment)
    // {
    //     $comment->delete();
    // }

}
