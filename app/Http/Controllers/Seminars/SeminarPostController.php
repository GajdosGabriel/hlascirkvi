<?php

namespace App\Http\Controllers\Seminars;

use App\Models\Seminar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SeminarPostController extends Controller
{
    public function show(Seminar $seminar)
    {
        return view('profile.seminars.show', ['seminar' => $seminar]);
    }
}
