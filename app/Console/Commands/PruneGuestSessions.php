<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Maže z tabuľky `sessions` nečinné relácie neprihlásených návštevníkov.
 *
 * Životnosť relácie (SESSION_LIFETIME) je dlhá kvôli prihláseným, každá
 * požiadavka bez cookie však založí nový riadok — boti z tisícov IP ich
 * nahromadili stovky tisíc za pár hodín. Hosť po dvoch hodinách nečinnosti
 * už nič nestratí: formulár pre hostí (App\Support\HumanCheck) aj tak
 * platí najviac dve hodiny. Prihlásených (user_id) sa mazanie netýka.
 */
class PruneGuestSessions extends Command
{
    protected $signature = 'sessions:prune-guests
        {--minutes=120 : Po koľkých minútach nečinnosti reláciu hosťa zmazať}
        {--time-budget=300 : Strop behu v sekundách (0 = bez stropu)}';

    protected $description = 'Delete idle guest sessions from the sessions table';

    /** Po dávkach, aby jeden DELETE nedržal zámok na celej tabuľke. */
    private const CHUNK = 5000;

    public function handle(): int
    {
        if (config('session.driver') !== 'database') {
            $this->info('PruneGuestSessions: relácie nie sú v databáze, nie je čo robiť.');

            return self::SUCCESS;
        }

        $start = microtime(true);
        $timeBudget = max(0, (int) $this->option('time-budget'));
        $cutoff = now()->subMinutes(max(1, (int) $this->option('minutes')))->getTimestamp();
        $table = config('session.table', 'sessions');

        $deleted = 0;

        do {
            $batch = DB::table($table)
                ->whereNull('user_id')
                ->where('last_activity', '<', $cutoff)
                ->limit(self::CHUNK)
                ->delete();

            $deleted += $batch;

            if ($timeBudget > 0 && microtime(true) - $start > $timeBudget) {
                break;
            }
        } while ($batch > 0);

        $this->info(sprintf('PruneGuestSessions: zmazaných %d relácií hostí', $deleted));

        return self::SUCCESS;
    }
}
