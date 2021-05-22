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

    public function store(SaveCommentsRequest $saveComments)
    {

        if ($saveComments->email) {
            (new EloquentUserRepository)->commentCheckIfUserAccountExist($saveComments);
        }

        $class = "App\\{$saveComments->input('model')}";
        $class = new $class;

        $post =  $class->whereId($saveComments->input('model_id'))->first();


        $reply = $saveComments->save($post);

        if (request()->expectsJson()) return $reply->load('user');

        return $reply;
    }

    public function update(Comment $comment, SaveCommentsRequest $request)
    {
        $comment->update($request->all());

        if (request()->expectsJson()) return $comment->load('user');

        return $comment;
    }


    public function storeEvent(SaveCommentsRequest $request, Event $event)
    {
        (new EloquentUserRepository)->commentCheckIfUserAccountExist($request);

        $reply = $event->comments()->create(array_merge($request->except(['email']), ['user_id' => auth()->user()->id]));

        if (request()->expectsJson()) {
            return $reply->load('user');
        };
        return back();
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        session()->flash('flash', 'Položka je zmazaná!');
    }
}
