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
        // Odkazy na úpravu a mazanie potrebujú len číslo kanála, a to nesie
        // samotná modlitba (`organization_id`). Cez vzťah sa brať nedá: kanál
        // je mäkko mazaný, takže pri modlitbe zo zmazaného kanála vracia null
        // a celý výpis padne.
        $prayers = Prayer::query()
            ->orderBy('created_at', 'desc')
            ->filter($filters)
            ->paginate(30)
            ->withQueryString();
        return view('admins.prayers.index',  compact('prayers'));
    }
}
