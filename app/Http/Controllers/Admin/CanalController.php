<?php

namespace App\Http\Controllers\Admin;

use App\Models\Canal;
use Illuminate\Http\Request;
use App\Filters\CanalFilters;
use App\Http\Controllers\Controller;

class CanalController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }
    public function index(CanalFilters $filters)
    {
        // Výpis siaha na obec aj správcov kanála (components/canal/list).
        // Bez eager loadu si ich pýtal riadok po riadku — pri 50 kanáloch
        // na stránku to bolo cez 200 dopytov namiesto piatich.
        $organizations = Canal::query()
            ->with(['village:id,fullname', 'users:id,first_name,last_name'])
            ->latest()
            ->filter($filters)
            ->paginate(50)
            ->withQueryString();

        return view('admins.canals.index', compact('organizations'));
    }
}
