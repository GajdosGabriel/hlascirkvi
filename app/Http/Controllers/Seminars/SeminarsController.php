<?php

namespace App\Http\Controllers\Seminars;

use App\Seminar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SeminarsController extends Controller
{
    public function index()
    {
        $seminars = Seminar::whereOrgId(auth()->user()->org_id)->get();

        return view('profiles.seminars', ['seminars' => $seminars]);
    }

    public function edit(Seminar $seminar)
    {
        return view('seminars.edit', compact('seminar'));
    }

    public function create()
    {
        return view('seminars.create', [ 'seminar' => new Seminar()]);
    }

    public function update(Seminar $seminar, Request $request)
    {
        $seminar->update($request->all());

        return redirect()->route('seminars.index');
    }

    public function store(Request $request)
    {
        Seminar::create(array_merge($request->all(), ['org_id' => auth()->user()->org_id]));

        return redirect()->route('seminars.index');
    }

    public function destroy(Seminar $seminar)
    {
        $seminar->posts()->detach();
        $seminar->delete();
        return redirect()->route('seminars.index');
    }
}
