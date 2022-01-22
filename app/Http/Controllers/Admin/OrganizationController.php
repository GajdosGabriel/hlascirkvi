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
        return view('admins.organizations.index', ['organizations' => Organization::latest()->filter($filters)->paginate(50)]);
    }
}
