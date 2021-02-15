<?php

namespace App\Http\Controllers;

use App\Prayer;
use Illuminate\Http\Request;

class PrayerController extends Controller
{
    public function index()
    {

        return view('prayer.index');
    }

    public function create()
    {
        return Prayer::latest()->paginate(7);
    }

    public function store(Request $request){
        dd($request->all());
//        Prayer::create($request->all());
    }
}
