<?php

namespace App\Http\Controllers\Admin;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrganizationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }
    public function index()
    {
        return view('admins.organizations.index', ['organizations' => Organization::latest()->paginate(50)]);
    }
}
