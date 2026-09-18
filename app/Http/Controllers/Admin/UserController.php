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
        return view('admins.users.index', [
            'users' => User::filter($filters)->paginate(50)->withQueryString(),
            'summary' => $this->summary(),
        ]);
    }

    /**
     * Súhrn nad celou tabuľkou (bez zrušených) pre dlaždice nad výpisom —
     * jeden agregačný dopyt plus rozpad podľa spôsobu prihlásenia.
     */
    private function summary(): object
    {
        $since = now()->subDays(UserFilters::RECENT_DAYS);

        $row = User::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(created_at >= ?) as fresh', [$since])
            ->selectRaw('SUM(last_login_at >= ?) as active', [$since])
            ->selectRaw('SUM(last_login_at IS NULL) as never')
            ->selectRaw('SUM(email_verified_at IS NULL) as unverified')
            ->selectRaw('SUM(status = ?) as pending', [ModelStatus::PendingReview->value])
            ->selectRaw('SUM(status = ? OR disabled = 1) as blocked', [ModelStatus::Blocked->value])
            ->first();

        return (object) [
            'total' => (int) $row->total,
            'fresh' => (int) $row->fresh,
            'active' => (int) $row->active,
            'never' => (int) $row->never,
            'unverified' => (int) $row->unverified,
            'pending' => (int) $row->pending,
            'blocked' => (int) $row->blocked,
            'via' => User::query()
                ->whereNotNull('last_login_via')
                ->toBase()
                ->selectRaw('last_login_via, COUNT(*) as total')
                ->groupBy('last_login_via')
                ->orderByDesc('total')
                ->pluck('total', 'last_login_via')
                ->map(fn ($count) => (int) $count)
                ->all(),
        ];
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
     * tu prepúšťalo aj heslo, canal_id či email_verified_at.
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
