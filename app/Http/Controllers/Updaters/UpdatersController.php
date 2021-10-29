<?php

namespace App\Http\Controllers\Updaters;

use App\Http\Controllers\Controller;
use App\Updater;
use Illuminate\Http\Request;

class UpdatersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('admins.updater.index', [ 'updaters' => Updater::get()] );
    }

    public function store(Request $request)
    {
        Updater::create($request->all());
        return back();
    }
}
