<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UserFilters;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }

    public function index(UserFilters $filters)
    {
        return view('admins.users.index', ['users' => User::latest()->filter($filters)->paginate(50)]);
    }
}
