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

    /**
     * Formulár (resources/views/users/edit.blade.php) posiela meno, priezvisko,
     * e-mail a prepínač `disabled`. `$request->all()` nad modelom s $guarded = []
     * tu prepúšťalo aj heslo, org_id či email_verified_at.
     */
    public function update(User $user, Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'disabled'   => 'nullable|boolean',
        ]);

        $user->update(collect($data)->except('disabled')->all());

        // `disabled` nie je v $fillable — blokovanie účtu je stavová zmena,
        // ktorú smie robiť len administrácia.
        $user->disabled = $request->boolean('disabled');
        $user->save();

        return redirect()->route('admin.user.index');
    }
}
