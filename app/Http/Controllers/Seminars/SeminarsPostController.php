<?php

namespace App\Http\Controllers\Seminars;

use App\Http\Controllers\Controller;
use App\Seminar;
use Illuminate\Http\Request;

class SeminarsPostController extends Controller
{
    public function index(Seminar $seminar)
    {
        return view('seminars.posts.index', compact('seminar'));
    }
}
