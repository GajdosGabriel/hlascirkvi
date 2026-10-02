<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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

        if (! $dryRun && $found) {
            $this->info(sprintf('Zneplatnených relácií: %d.', $this->invalidateSessions($found)));
        }

        $this->info(sprintf(
            '%s %d účtov so slabým heslom.',
            $dryRun ? 'Nájdených (dry-run, nič sa nezmenilo):' : 'Prepísaných:',
            count($found)
        ));

        return self::SUCCESS;
    }

    /**
     * Zmena hesla sama nezruší už prihlásené cookies — relácie týchto účtov
     * sa mažú, aby nikto s uhádnutým heslom neostal prihlásený.
     *
     * @param  int[]  $userIds
     */
    private function invalidateSessions(array $userIds): int
    {
        $driver = config('session.driver');

        if ($driver === 'database') {
            return DB::table(config('session.table', 'sessions'))->whereIn('user_id', $userIds)->delete();
        }

        if ($driver !== 'file') {
            $this->warn("Ovládač relácií '{$driver}' sa nedá prehľadať — relácie zneplatnite ručne.");

            return 0;
        }

        $key = Auth::guard('web')->getName();
        $ids = array_flip(array_map('strval', $userIds));
        $removed = 0;

        foreach (File::files(config('session.files')) as $file) {
            $data = @unserialize((string) @file_get_contents($file->getPathname()));

            if (is_array($data) && isset($data[$key]) && isset($ids[(string) $data[$key]])) {
                File::delete($file->getPathname());
                $removed++;
            }
        }

        return $removed;
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
