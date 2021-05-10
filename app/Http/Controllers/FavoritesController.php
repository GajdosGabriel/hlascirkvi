<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use App\Event;
use App\Prayer;
use App\Comment;
use App\Organization;
use Illuminate\Http\Request;
use App\Notifications\Prayer\FavoritePrayer;
use Illuminate\Support\Facades\Notification;
use App\Repositories\Eloquent\EloquentUserRepository;

class FavoritesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('favoritePrayer');
    }

    public function favoritePosts(Post $post) {
        $post->favorite();
        return redirect()->route('post.show', [$post->id, $post->slug]);
    }

    public function favoriteUsers(User $user) {
        $user->favorite();
        session()->flash('flash', 'Priateľstvo s autorom!');
        return back();
    }

    public function favoriteComments(Comment $comment) {
        $comment->favorite();
        return back();
    }

    public function favoriteOrganizations(Organization $organization) {
        $organization->favorite();
        session()->flash('flash', 'Sledovanie potvrdené!');
        return back();
    }

    public function storeEventsRecords(Event $event) {
        $event->favorite();
        return redirect()->route('event.show', [$event->id, $event->slug]);
    }

    public function favoritePrayer(Prayer $prayer, Request $request)
    {
        if($request->email) {
            (new EloquentUserRepository)->commentCheckIfUserAccountExist($request);
        }

        $prayer->favorite();
        session()->flash('flash', 'Sledovanie potvrdené!');

        Notification::send(User::whereId( $prayer->user_id)->first(), new FavoritePrayer($prayer));
    }




}
