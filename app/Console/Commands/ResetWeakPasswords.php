<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Prepíše heslá, ktoré aplikácia kedysi dávala účtom napevno.
 *
 * - Účty z Facebooku dostávali Hash::make(rand(8,10)), teda "8", "9" alebo "10".
 * - Komentár bez registrácie zakladal účet s heslom "registracnyformularheslo".
 *
 * Do takého účtu sa dalo prihlásiť formulárom len so znalosťou e-mailu. Nové
 * heslo je náhodné, nikto ho nepozná; vlastník sa prihlási cez Google/Facebook
 * alebo si heslo nastaví cez obnovu. Zmazaný remember_token odhlási aj
 * zapamätané prihlásenia, ktoré mohli vzniknúť zneužitím.
 *
 * Kontrola ide cez password_verify: bcrypt je zámerne pomalý, takže pár sto
 * účtov trvá rádovo minúty. Príkaz je idempotentný — dá sa pustiť znova.
 */
class ResetWeakPasswords extends Command
{
    protected $signature = 'app:users-reset-weak-passwords
        {--dry-run : Len vypíše, koľko účtov má slabé heslo, nič nemení}';

    protected $description = 'Replace hard-coded legacy passwords (social login, comments) with random ones';

    private const WEAK = ['8', '9', '10', 'registracnyformularheslo'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $found = [];

        $bar = $this->output->createProgressBar(DB::table('users')->count());

        DB::table('users')->select('id', 'password')->orderBy('id')
            ->chunkById(200, function ($users) use (&$found, $bar, $dryRun) {
                foreach ($users as $user) {
                    $bar->advance();

                    if (!$this->isWeak((string) $user->password)) {
                        continue;
                    }

                    $found[] = $user->id;

                    if (!$dryRun) {
                        DB::table('users')->where('id', $user->id)->update([
                            'password' => Hash::make(Str::random(40)),
                            'remember_token' => null,
                        ]);
                    }
                }
            });

        $bar->finish();
        $this->newLine(2);

        // Len ID — výstup z produkcie končí v logoch a e-maily tam nepatria.
        if ($found) {
            $this->line('ID: ' . implode(', ', $found));
        }

        $this->info(sprintf(
            '%s %d účtov so slabým heslom.',
            $dryRun ? 'Nájdených (dry-run, nič sa nezmenilo):' : 'Prepísaných:',
            count($found)
        ));

        return self::SUCCESS;
    }

    private function isWeak(string $hash): bool
    {
        foreach (self::WEAK as $plain) {
            if (password_verify($plain, $hash)) {
                return true;
            }
        }

        return false;
    }
}
