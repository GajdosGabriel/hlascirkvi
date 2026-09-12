<?php

namespace App\Http\Controllers\Public;

use App\Enums\PostSection;
use Cache;
use \Alaouy\Youtube;
use App\Models\Seminar;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        //
    }


    public function zivePrenosy(EloquentPostRepository $posts)
    {
        session()->forget('lastVisit');

        session()->forget('countUnwatchedVideos');
        $posts = $posts->groupedBySection(PostSection::Live);
        return view('pages.online-prenosy', compact('posts'));
    }

    public function seminare()
    {
        // Výpis vykresľuje kartu ku každému príspevku seminára. Bez eager loadu
        // si každý seminár vypýtal svoje príspevky — aj s ich obrázkami,
        // kanálmi a obľúbenými — vlastnou sériou dopytov.
        $seminars = Seminar::whereNotNull('published')
            ->with('posts')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.seminare', compact('seminars'));
    }

    public function gdpr()
    {
        return view('pages.ochrana-osobnych-udajov');
    }
}
