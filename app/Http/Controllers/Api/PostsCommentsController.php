<?php

namespace App\Http\Controllers\Api;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCommentsRequest;
use App\Http\Resources\CommentResource;
use App\Repositories\Eloquent\EloquentUserRepository;

class PostsCommentsController extends Controller
{
    public function store(Post $post, SaveCommentsRequest $saveComments)
    {  

        if ($saveComments->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($saveComments);
        }

        // $class = "App\\{$saveComments->input('model')}";
        // $class = new $class;

        // $post =  $class->whereId($saveComments->input('model_id'))->first();


        $comment = $saveComments->save($post);

        return new CommentResource($comment);

    }
}
