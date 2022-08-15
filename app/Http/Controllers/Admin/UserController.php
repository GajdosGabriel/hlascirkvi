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
        return view('admins.users.index', ['users' => User::latest()->filter($filters)->paginate(50)->withQueryString()]);
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $user->update($request->all());
        return redirect()->route('admin.users.index');
    }
}
