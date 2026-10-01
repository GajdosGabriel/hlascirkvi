<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Zabudne IP adresu posledného prihlásenia po uplynutí lehoty. Slúži na
 * bezpečnostné vyšetrenie zneužitia účtu, nie na trvalú evidenciu.
 */
class PruneLoginIps extends Command
{
    protected $signature = 'users:prune-login-ips {--days=90 : Koľko dní ponechať}';

    protected $description = 'Null last_login_ip for users who have not logged in for a while';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $count = DB::table('users')
            ->whereNotNull('last_login_ip')
            ->where(fn ($q) => $q
                ->where('last_login_at', '<', now()->subDays($days))
                ->orWhereNull('last_login_at'))
            ->update(['last_login_ip' => null]);

        $this->info("PruneLoginIps: vymazaných IP adries: {$count}");

        return self::SUCCESS;
    }
}
