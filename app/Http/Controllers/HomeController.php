<?php

namespace App\Http\Controllers;

use Cache;
use App\Tag;
use Carbon\Carbon;
use \Alaouy\Youtube;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Contracts\OrganizationRepository;
use App\Repositories\Eloquent\EloquentOrganizationRepository;



class HomeController extends Controller
{

    public function __construct()
    {
        $this->posts = new EloquentPostRepository;
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.home');
    }

    public function zivePrenosy()
    {
        session()->forget('lastVisit');

        session()->forget('countUnwatchedVideos');
        $posts = $this->posts->getPostsByUpdater(16);
        return view('pages.online-prenosy', compact('posts'));
    }

    public function konferencieApute() {

        $tags = Tag::all();

        return view('tags.index', compact('tags'));

        // $posts = $this->posts->getPostsByUpdater(17);
        // return view('pages.vzdelavanie', compact('posts'));
    }

    public function gdpr(){
        return view('pages.ochrana-osobnych-udajov');
    }

}
