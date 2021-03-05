<?php

namespace App\Http\Controllers;

use App\Organization;
use App\Post;
use App\Event;
use App\Prayer;
use App\Repository\User\UserRegistration;
use App\User;
use App\Comment;
use Illuminate\Http\Request;

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

    public function favoritePrayer(Prayer $prayer, Request $request) {
        if($request->email) {
            (new UserRegistration)->commentCheckIfUserAccountExist($request);
        }

        $prayer->favorite();
        session()->flash('flash', 'Sledovanie potvrdené!');
    }




}
