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
        // Výpis skladá odkazy na úpravu a mazanie cez $prayer->organization->id
        // (admins/prayers/index.blade.php:26 a :31) — bez eager loadu to bol
        // dopyt na každý riadok.
        $prayers = Prayer::query()
            ->with('organization:id')
            ->orderBy('created_at', 'desc')
            ->filter($filters)
            ->paginate(30)
            ->withQueryString();
        return view('admins.prayers.index',  compact('prayers'));
    }
}
