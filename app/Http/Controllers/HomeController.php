<?php

namespace App\Http\Controllers;

use Cache;
use \Alaouy\Youtube;
use App\Models\Seminar;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentPostRepository;

class HomeController extends Controller
{
    public function __construct()
    {
        //
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

    public function zivePrenosy(EloquentPostRepository $posts)
    {
        session()->forget('lastVisit');

        session()->forget('countUnwatchedVideos');
        $posts = $posts->getPostsByUpdater(16);
        return view('pages.online-prenosy', compact('posts'));
    }

    public function seminare()
    {
        $seminars = Seminar::whereNotNull('published')->orderBy('created_at', 'desc')->get();

        //  Staré zoradovanie
        // $posts = $this->posts->getPostsByUpdater(17);
        return view('pages.seminare', compact('seminars'));
    }

    public function gdpr()
    {
        return view('pages.ochrana-osobnych-udajov');
    }
}
