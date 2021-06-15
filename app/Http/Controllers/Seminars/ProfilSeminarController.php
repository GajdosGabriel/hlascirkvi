<?php

namespace App\Http\Controllers\Seminars;

use App\Seminar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfilSeminarController extends Controller
{
    public function index()
    {
        $seminars = Seminar::withCount('posts')
                    ->whereOrganizationId(auth()->user()->org_id)
                    ->orderBy('created_at', 'desc')->get();

        return view('profiles.seminars', ['seminars' => $seminars]);
    }

    public function show(Seminar $profilSeminar)
    {
        return view('profiles.seminars.show', ['seminar' => $profilSeminar]);
    }
}
