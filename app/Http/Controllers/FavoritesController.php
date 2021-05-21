<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use App\Event;
use App\Prayer;
use App\Comment;
use App\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Prayer\FavoriteForOwner;
use App\Notifications\Prayer\FavoriteForUsers;
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
        return redirect()->route('akcie.show', [$event->id, $event->slug]);
    }

    public function favoritePrayer(Prayer $prayer, Request $request)
    {
        if($request->email) {
            (new EloquentUserRepository)->commentCheckIfUserAccountExist($request);
        }

        $prayer->favorite();
        session()->flash('flash', 'Prihlásenie potvrdené!');

        // Info for owner
        Notification::send($prayer->user, new FavoriteForOwner($prayer));

        // Info for another conected users
        foreach($prayer->favorites as $favorite)
        {
           $user = User::whereId($favorite->user_id)->first();

            Notification::send($user, new FavoriteForUsers($prayer));
        }

    }




}
