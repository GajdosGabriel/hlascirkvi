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



    public function store(SaveCommentsRequest $saveComments)
    {

        if ($saveComments->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($saveComments);
        }

        $class = "App\\Models\\{$saveComments->input('model')}";
        $class = new $class;

        $post =  $class->whereId($saveComments->input('model_id'))->first();


        $reply = $saveComments->save($post);

        if (!$reply->user_id == auth()->user()->id) {
            $reply->user->notify(new CreatedNewComment($reply));
        }

        if (request()->expectsJson()) return $reply->load('user');

        return $reply;
    }
}
