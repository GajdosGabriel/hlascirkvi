<?php

namespace App\Http\Controllers\Seminars;

use App\Seminar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfilSeminarController extends Controller
{

    public function show(Seminar $profileSeminar)
    {
        return view('profiles.seminars.show', ['seminar' => $profileSeminar]);
    }
}
