<?php

namespace App\Http\Controllers\Api;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCommentsRequest;
use App\Repositories\Eloquent\EloquentUserRepository;

class PostsCommentsController extends Controller
{
    public function store(Post $post, SaveCommentsRequest $saveComments)
    {  

        if ( $saveComments->email) {
          $user =  (new EloquentUserRepository)->checkIfUserAccountExist($saveComments->email);
        }

        dd($user);

        // $class = "App\\{$saveComments->input('model')}";
        // $class = new $class;

        // $post =  $class->whereId($saveComments->input('model_id'))->first();

        $reply = $saveComments->save($post);

        return $reply;
    }
}
