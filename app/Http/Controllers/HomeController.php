<?php

namespace App\Http\Controllers;

use \Alaouy\Youtube;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Contracts\OrganizationRepository;



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

        $posts = $this->posts->getPostsByUpdater(17);
        return view('pages.vzdelavanie', compact('posts'));
    }

    public function gdpr(){
        return view('pages.ochrana-osobnych-udajov');
    }

}
