<?php

namespace App\Http\Controllers\Admin;

use App\Models\Prayer;
use Illuminate\Http\Request;
use App\Filters\PrayerFilters;
use App\Http\Controllers\Controller;

class PrayerController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }

    public function index(PrayerFilters $filters)
    {
        $prayers = Prayer::orderBy('created_at', 'desc')->filter($filters)->paginate(30);
        return view('admins.prayers.index',  compact('prayers'));
    }
}
