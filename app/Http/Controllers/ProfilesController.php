<?php

namespace App\Http\Controllers;

use App\Post;
use App\Event;
use App\Seminar;
use App\Messenger;
use App\Organization;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentOrganizationRepository;

class ProfilesController extends Controller
{
    public function __construct()
    {
        $this->organization = new EloquentOrganizationRepository;
    }

    public function home()
    {
        return view('profiles.home');
    }



    public function organizations()
    {
        // dd($user);
        // if(auth()->id() != $user) {
        //    abort(403);
        // }
        $organizations =  $this->organization->usersOrganizations(auth()->user()->id)->paginate(30);
        return view('organizations.user-index', compact('organizations'));
    }

    public function posts()
    {
        $posts = Post::whereOrganizationId(auth()->user()->org_id)->latest()->paginate(30);

        return view('profiles.posts.index', compact('posts'));
    }




    public function events()
    {
        $events = Event::whereOrganizationId(auth()->user()->org_id)
                    ->latest()->paginate(30);
        return view('profiles.events.index', compact('events'));
    }

    public function seminars()
    {
        $seminars = Seminar::withCount('posts')
                    ->whereOrganizationId(auth()->user()->org_id)
                    ->orderBy('created_at', 'desc')->get();

        return view('profiles.seminars', ['seminars' => $seminars]);
    }
}
