<?php

namespace App\Console\Commands;

use App\Models\Prayer;
use App\Services\Prayers\SpamDetector;
use Illuminate\Console\Command;

class PrayerDespam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prayer:despam
                            {--delete : Nájdené prosby zmazať (soft delete)}
                            {--since= : Brať len prosby vytvorené od tohto dátumu, napr. 2026-01-01}
                            {--with-trashed : Prejsť aj už zmazané — na overenie, čo by príkaz našiel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nájde reklamný spam medzi prosbami o modlitbu (pôžičky, podvodné inzeráty, holé odkazy)';

    public function handle(SpamDetector $detector): int
    {
        $query = $this->option('with-trashed') ? Prayer::withTrashed() : Prayer::query();

        if ($since = $this->option('since')) {
            $query->where('created_at', '>=', $since);
        }

        $found = [];

        // chunkById, lebo prosieb sú stovky tisíc a --delete mení `deleted_at`
        // priamo pod kurzorom; stránkovanie cez offset by riadky preskakovalo.
        $query->orderBy('id')->chunkById(500, function ($prayers) use ($detector, &$found) {
            foreach ($prayers as $prayer) {
                if ($hit = $detector->inspect($prayer->title, $prayer->body)) {
                    $found[] = [$prayer, $hit];
                }
            }
        });

        if ($found === []) {
            $this->info('Žiadny spam sa nenašiel.');

            return self::SUCCESS;
        }

        $this->table(
            ['id', 'dátum', 'autor', 'dôvod', 'signály', 'text'],
            array_map(fn ($row) => [
                $row[0]->id,
                optional($row[0]->created_at)->format('Y-m-d'),
                mb_strimwidth((string) $row[0]->user_name, 0, 16, '…'),
                $row[1]['reason'],
                implode(', ', $row[1]['signals']),
                mb_strimwidth(preg_replace('/\s+/u', ' ', $row[0]->body), 0, 60, '…'),
            ], $found),
        );

        $this->newLine();
        $this->info(sprintf('Nájdených: %d', count($found)));

        if (! $this->option('delete')) {
            $this->comment('Náhľad — nič sa nezmazalo. Spusti s --delete.');

            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($found as [$prayer, $hit]) {
            if ($prayer->trashed()) {
                continue;
            }

            $prayer->delete();
            $deleted++;
        }

        $this->info(sprintf('Zmazaných: %d (soft delete, dajú sa obnoviť)', $deleted));

        return self::SUCCESS;
    }
}
