<?php

namespace App\Http\Controllers\Admin;

use App\Filters\SystemLogFilters;
use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

/**
 * Denník udalostí — čo komu odišlo, čo zlyhalo, prihlásenia, cron.
 * Zapisuje App\Listeners\SystemLogSubscriber a App\Services\SystemLog\Recorder.
 */
class SystemLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }

    public function index(SystemLogFilters $filters)
    {
        $logs = SystemLog::with('user:id,first_name,last_name,email')
            ->orderByDesc('id')
            ->filter($filters)
            ->paginate(50)
            ->withQueryString();

        return view('admins.logs.index', [
            'logs' => $logs,
            'summary' => $this->summary(),
        ]);
    }

    /** Kanály, ktoré sa v denníku reálne vyskytujú — pre výber vo filtri. */
    public static function channels(): array
    {
        return DB::table('system_logs')->distinct()->orderBy('channel')->pluck('channel', 'channel')->all();
    }

    private function summary(): object
    {
        return DB::table('system_logs')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw("coalesce(sum(event = 'mail.sent' and created_at >= ?), 0) as sent_day", [now()->subDay()])
            ->selectRaw("coalesce(sum(event = 'mail.sent'), 0) as sent_week")
            ->selectRaw("coalesce(sum(event = 'mail.failed' and created_at >= ?), 0) as failed_day", [now()->subDay()])
            ->selectRaw("coalesce(sum(event = 'mail.failed'), 0) as failed_week")
            ->selectRaw("coalesce(sum(level = 'error' and created_at >= ?), 0) as errors_day", [now()->subDay()])
            ->selectRaw("coalesce(sum(level = 'error'), 0) as errors_week")
            ->selectRaw("coalesce(sum(event in ('auth.failed', 'auth.lockout') and created_at >= ?), 0) as auth_failed_day", [now()->subDay()])
            ->selectRaw("coalesce(sum(event = 'auth.login' and created_at >= ?), 0) as logins_day", [now()->subDay()])
            ->first();
    }
}
