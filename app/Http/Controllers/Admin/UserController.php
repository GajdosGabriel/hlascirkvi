<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModelStatus;
use App\Filters\UserFilters;
use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }

    public function index(UserFilters $filters)
    {
        if (request()->boolean('pending')) {
            $legacy = DB::table('pending_users')->select('id', 'email', 'created_at', 'kind')->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(snapshot, '$.user.uuid')) as uuid");
            $pending = DB::table('pending_registrations')->select('id', 'email', 'created_at')->selectRaw("'registration' as kind, NULL as uuid")
                ->unionAll($legacy)->orderByDesc('created_at')->paginate(50)->withQueryString();

            return view('admins.users.pending', compact('pending'));
        }
        $users = User::filter($filters)->paginate(50)->withQueryString();
        if ($users->currentPage() > 1 && $users->isEmpty()) {
            return redirect()->to($users->url($users->lastPage()));
        }

        return view('admins.users.index', [
            'users' => $users,
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

    public function show($user)
    {
        $user = User::withTrashed()->findOrFail($user);
        $user->load([
            'canal' => fn ($query) => $query->withTrashed(),
            'canals' => fn ($query) => $query->withTrashed()->withCount(['posts', 'prayers'])->orderBy('title'),
        ])->loadCount(['commentss', 'savedPosts']);

        $statusChangedBy = $user->status_changed_by
            ? User::withTrashed()->find($user->status_changed_by) : null;
        $logs = SystemLog::query()->where(fn ($query) => $query
            ->where('user_id', $user->id)->orWhere('recipient', $user->email))
            ->orderByDesc('id')->paginate(15, ['id', 'event', 'level', 'status', 'message', 'ip', 'created_at'], 'events_page');
        $comments = $user->commentss()->withTrashed()->orderByDesc('id')
            ->paginate(10, ['id', 'body', 'published', 'deleted_at', 'created_at'], 'comments_page');
        $sessions = config('session.driver') === 'database'
            ? DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->where('last_activity', '>=', now()->subMinutes((int) config('session.lifetime'))->timestamp)
                ->orderByDesc('last_activity')->limit(20)->get(['ip_address', 'user_agent', 'last_activity'])
            : null;

        return view('admins.users.show', [
            'user' => $user,
            'statusChangedBy' => $statusChangedBy,
            'permissions' => $user->getAllPermissions()->pluck('name')->sort()->values(),
            'logs' => $logs->withQueryString(),
            'comments' => $comments->withQueryString(),
            'sessions' => $sessions,
            'activity' => [
                'Označenia a sledovania' => DB::table('favorites')->where('user_id', $user->id)->count(),
                'Odoslané správy' => DB::table('messengers')->where('user_id', $user->id)->count(),
                'Prijaté správy' => DB::table('messengers')->where('requested_user', $user->id)->count(),
                'Platné API prístupy' => $user->tokens()->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            ],
        ]);
    }

    public function edit($user)
    {
        if (DB::table('pending_users')->where('id', $user)->exists()) {
            return redirect()->route('admin.user.index', ['pending' => 1]);
        }
        $user = User::findOrFail($user);

        return view('users.edit', [
            'user' => $user,
            'statuses' => User::statusOptions(),
        ]);
    }

    /**
     * Zapisujeme iba validované profilové údaje a stav účtu.
     */
    public function update(User $user, Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'description' => 'nullable|string|max:5000',
            'status' => ['required', Rule::in(array_column(User::statusOptions(), 'value'))],
            'status_reason' => 'nullable|required_unless:status,active|string|max:500',
        ], [
            'first_name.required' => 'Zadajte meno používateľa.',
            'first_name.max' => 'Meno môže mať najviac 50 znakov.',
            'last_name.max' => 'Priezvisko môže mať najviac 50 znakov.',
            'email.required' => 'Zadajte e-mailovú adresu.',
            'email.email' => 'Zadajte platnú e-mailovú adresu.',
            'email.unique' => 'Túto e-mailovú adresu už používa iný účet.',
            'email.max' => 'E-mail môže mať najviac 100 znakov.',
            'description.max' => 'Popis profilu môže mať najviac 5000 znakov.',
            'status_reason.required_unless' => 'Pri neaktívnom účte uveďte dôvod zmeny stavu.',
            'status_reason.max' => 'Dôvod môže mať najviac 500 znakov.',
        ]);

        $newStatus = ModelStatus::from($data['status']);

        if ($user->is($request->user()) && ! $newStatus->isActive()) {
            return back()->withInput()->withErrors([
                'status' => 'Nemôžete zneprístupniť vlastný administrátorský účet.',
            ]);
        }

        $user->fill(collect($data)->except(['status', 'status_reason'])->all());

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
