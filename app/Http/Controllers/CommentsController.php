<?php

namespace App\Http\Controllers;

use App\Post;
use App\Event;
use App\Comment;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Http\Request;
use App\Notifications\Comments;
use App\Http\Requests\SaveCommentsRequest;

class CommentsController extends Controller
{
    protected $users;

    public function __construct(EloquentUserRepository $users)
    {
        $this->users = $users;
        //        $this->middleware('auth');
    }

    

   

  
}
