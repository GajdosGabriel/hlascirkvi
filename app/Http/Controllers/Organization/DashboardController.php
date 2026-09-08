<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardStats;
use Illuminate\Contracts\View\View;

/**
 * Nástenka prihláseného správcu kanála (/dashboard).
 *
 * Nahradila pôvodné /profile, ktoré bolo len prázdna stránka s tromi číslami
 * a dvoma zástupnými oknami. Stará adresa naďalej existuje ako presmerovanie
 * (routes/web.php).
 */
class DashboardController extends Controller
{
    public function __invoke(DashboardStats $stats): View
    {
        $organization = auth()->user()->organization;

        // Užívateľ bez prideleného kanála sa sem dostane tiež — má vidieť, čo
        // s tým, nie prázdne dlaždice s nulami.
        if (! $organization) {
            return view('dashboard.index', ['organization' => null]);
        }

        return view('dashboard.index', [
            'organization' => $organization,
        ] + $stats->for($organization));
    }
}
