<?php

namespace App\Http\Controllers\Seminars;

use App\Seminar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SeminarPostController extends Controller
{
    public function show(Seminar $seminar)
    {
        return view('profiles.seminars.show', ['seminar' => $seminar]);
    }
}
