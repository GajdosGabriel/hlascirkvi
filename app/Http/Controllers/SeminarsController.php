<?php

namespace App\Http\Controllers;

use App\Seminar;
use Illuminate\Http\Request;

class SeminarsController extends Controller
{
    public function index()
    {
        $seminars = Seminar::all();

        return view('profiles.seminars', compact('seminars'));
    }

    public function edit(Seminar $seminar)
    {
        return view('seminars.edit', compact('seminar'));
    }

    public function store(Request $request)
    {
        Seminar::create(array_merge($request->all(), ['org_id' => auth()->user()->org_id] ));

        return  back();
    }

    public function destroy(Seminar $seminar)
    {
        $seminar->delete();
    }
}
