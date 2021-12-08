<?php

namespace App\Http\Controllers\Api;

use App\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Requests\SaveCommentsRequest;
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

        $class = "App\\{$saveComments->input('model')}";
        $class = new $class;

        $post =  $class->whereId($saveComments->input('model_id'))->first();


        $reply = $saveComments->save($post);

        if (request()->expectsJson()) return $reply->load('user');

        return $reply;
    }


  
}
