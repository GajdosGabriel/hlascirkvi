<?php

namespace App\Http\Controllers;

use App\Post;
use App\Event;
use App\Comment;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repository\User\UserRegistration;
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

    public function store(SaveCommentsRequest $saveComments, Post $post)
    {
        if($saveComments->email) {
            (new UserRegistration)->commentCheckIfUserAccountExist($saveComments);
        }

        $reply = $saveComments->save($post);

        if(request()->expectsJson()) return $reply->load('user');

        return back();
    }

    public function updateComment(SaveCommentsRequest $request, Comment $comment)
    {
       $comment->update($request->all());

        if(request()->expectsJson()) return $comment->load('user');

        return back();
    }


    public function storeEvent(SaveCommentsRequest $request, Event $event)
    {
        (new UserRegistration)->commentCheckIfUserAccountExist($request);


        $reply = $event->comments()->create(array_merge($request->except(['email']), ['user_id' => auth()->user()->id ]));

        if(request()->expectsJson()) {
            return $reply->load('user');
        };
        return back();
    }

    public function destroyComment(Comment $comment) {
        $comment->delete();

        session()->flash('flash', 'Položka je zmazaná!');

        return back();
    }
}
