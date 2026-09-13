<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModelStatus;
use App\Filters\UserFilters;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        return view('users.edit', [
            'user' => $user,
            'statuses' => User::statusOptions(),
        ]);
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
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'status' => ['required', Rule::in(array_column(User::statusOptions(), 'value'))],
            'status_reason' => 'nullable|required_unless:status,active|string|max:500',
        ]);

        $newStatus = ModelStatus::from($data['status']);

        if ($user->is($request->user()) && ! $newStatus->isActive()) {
            return back()->withInput()->withErrors([
                'status' => 'Nemôžete zneprístupniť vlastný administrátorský účet.',
            ]);
        }

        $user->update(collect($data)->except(['status', 'status_reason'])->all());

        if ($user->status !== $newStatus) {
            $user->status_changed_at = now();
            $user->status_changed_by = $request->user()->id;
        }

        $user->status = $newStatus;
        $user->status_reason = $data['status_reason'] ?? null;
        // Do odstránenia starého stĺpca ho synchronizujeme pre integrácie,
        // ktoré ešte rozumejú iba blokácii áno/nie.
        $user->disabled = $newStatus === ModelStatus::Blocked;
        $user->save();

        if (! $newStatus->isActive()) {
            $user->tokens()->delete();
        }

        return redirect()->route('admin.user.index')->with('flash', 'Používateľský účet bol aktualizovaný.');
    }
}
