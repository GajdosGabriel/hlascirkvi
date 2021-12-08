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

    public function index(Post $post)
    {
        return CommentResource::collection($post->comments);
    }
    
    public function store(Post $post, SaveCommentsRequest $saveComments)
    {  

        if ($saveComments->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($saveComments);
        }

        $comment = $saveComments->save($post);

        return new CommentResource($comment);

    }
}
