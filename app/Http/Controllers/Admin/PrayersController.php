<?php

namespace App\Http\Controllers\Admin;

use App\Models\Prayer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PrayersController extends Controller
{
    public function index()
    {
        $prayers = Prayer::orderBy('created_at', 'desc')->paginate(30);
        return view('admins.prayers.index',  compact('prayers'));
    }
}
