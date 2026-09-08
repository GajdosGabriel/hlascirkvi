<?php

namespace App\Http\Controllers\Admin;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Filters\OrganizationFilters;
use App\Http\Controllers\Controller;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }
    public function index(OrganizationFilters $filters)
    {
        // Výpis siaha na obec, updaterov aj správcov kanála
        // (organizations/_organization-table.blade.php:30, 32, 69). Bez eager
        // loadu si ich pýtal riadok po riadku — pri 50 kanáloch na stránku
        // to bolo cez 200 dopytov namiesto piatich.
        $organizations = Organization::query()
            ->with(['village:id,fullname', 'updaters:id,title,slug,type', 'users:id,first_name,last_name'])
            ->latest()
            ->filter($filters)
            ->paginate(50)
            ->withQueryString();

        return view('admins.organizations.index', compact('organizations'));
    }
}
