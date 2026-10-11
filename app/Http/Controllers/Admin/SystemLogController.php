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
    /** Šablóny e-mailov podľa triedy notifikácie (context.class). */
    private const MAIL_TEMPLATES = [
        'ResetPassword' => 'Obnova hesla',
        'ConfirmEmail' => 'Potvrdenie e-mailovej adresy',
        'ConfirmRegistration' => 'Dokončenie registrácie',
        'ConfirmFavorite' => 'Potvrdenie obľúbených',
        'NewRegistration' => 'Nová registrácia (pre správcu)',
        'ConfirmPrayer' => 'Potvrdenie modlitby',
        'ConfirmFulfilledPrayer' => 'Potvrdenie vypočutej modlitby',
        'PrayerFulfilledOrNotYet' => 'Otázka, či bola modlitba vypočutá',
        'NewPrayer' => 'Nová modlitba (pre správcu)',
        'ConfirmComment' => 'Potvrdenie komentára',
        'CreatedNewComment' => 'Nový komentár pri príspevku',
        'RepliedToComment' => 'Odpoveď na komentár',
        'GuestRepliedToComment' => 'Odpoveď na komentár (neprihlásený autor)',
        'UnpublishedComment' => 'Komentár čaká na schválenie',
        'InappropriateComment' => 'Skrytý komentár',
        'ComingNewMessage' => 'Nová súkromná správa',
        'PortalMail' => 'Správa z portálu',
        'CanalMessage' => 'Správa pre kanál',
        'CanalProfileCompleted' => 'Doplnený profil kanála',
        'BufeerIsEmpty' => 'Prázdny zásobník videí',
        'YoutubeApiKeyFailed' => 'YouTube API odmieta kľúč',
        'YoutubeImportIssue' => 'Problém s importom z YouTube',
        'YoutubeSourceMissing' => 'Chýbajúci zdroj YouTube',
        'PostNewsletter' => 'Mesačný newsletter',
    ];

    public function __construct()
    {
        $this->middleware('checkSuperAdmin');
    }

    public function index(SystemLogFilters $filters)
    {
        // Telá mailov sa v zozname neťahajú, stačí vedieť, či nejaké je.
        $logs = SystemLog::with('user:id,first_name,last_name,email')
            ->select(['id', 'created_at', 'level', 'channel', 'event', 'status', 'message', 'recipient', 'user_id', 'subject_type', 'subject_id', 'context', 'ip'])
            ->orderByDesc('id')
            ->filter($filters)
            ->paginate(50)
            ->withQueryString();

        if ($logs->currentPage() > 1 && $logs->isEmpty()) {
            return redirect()->to($logs->url($logs->lastPage()));
        }

        return view('admins.logs.index', [
            'logs' => $logs,
            'summary' => $this->summary(),
        ]);
    }

    /** Detail odoslaného e-mailu — hlavička a telo tak, ako odišlo príjemcovi. */
    public function show(SystemLog $log)
    {
        abort_unless($log->channel === 'mail', 404);

        $class = class_basename((string) ($log->context['class'] ?? ''));

        return view('admins.logs.show', [
            'log' => $log->load('user:id,first_name,last_name,email'),
            'template' => self::MAIL_TEMPLATES[$class] ?? ($class ?: 'Vlastný e-mail'),
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
