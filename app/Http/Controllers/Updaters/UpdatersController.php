<?php

namespace App\Http\Controllers\Updaters;

use App\Models\Updater;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
