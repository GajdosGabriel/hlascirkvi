<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Maže exspirované položky cache: súbory súborovej cache
 * (storage/framework/cache/data) aj riadky tabuľky `cache`, kde sú
 * počítadlá obmedzovača požiadaviek (cache.limiter).
 *
 * Ani FileStore, ani DatabaseStore exspirovanú položku nezmaže, kým ju
 * niekto neprečíta. Kľúče, ktoré sa už nikdy neprečítajú (počítadlá
 * throttle pre každú IP, odpovede na jednorazové dopyty), preto ostávajú
 * navždy. Pri návale botov z tisícov IP tak v súborovej cache vzniklo
 * ~8 miliónov drobných súborov a 68 GB na disku — súbor má pár bajtov,
 * hosting však za každý účtuje celý blok.
 *
 * Každý súbor začína 10-cifernou unixovou časovou pečiatkou exspirácie
 * (9999999999 = navždy), takže stačí prečítať jeho začiatok.
 */
class CachePruneExpired extends Command
{
    protected $signature = 'cache:prune-expired
        {--dir=* : Zo súborovej cache len tieto podadresáre (napr. --dir=7a --dir=ff)}
        {--time-budget=0 : Strop behu súborovej časti v sekundách (0 = bez stropu)}';

    protected $description = 'Delete expired entries of the file and database cache stores';

    /** Po dávkach, aby jeden DELETE nedržal zámok na celej tabuľke. */
    private const CHUNK = 5000;

    public function handle(): int
    {
        $rows = $this->pruneDatabase();
        [$deleted, $kept] = $this->pruneFiles();

        $this->info(sprintf(
            'CachePruneExpired: súbory zmazané %d, ponechané %d; riadky zmazané %d',
            $deleted,
            $kept,
            $rows,
        ));

        return self::SUCCESS;
    }

    private function pruneDatabase(): int
    {
        $store = config('cache.stores.database');
        $table = $store['table'] ?? 'cache';
        $connection = DB::connection($store['connection'] ?? null);

        if (! Schema::connection($connection->getName())->hasTable($table)) {
            return 0;
        }

        $deleted = 0;

        do {
            $batch = $connection->table($table)
                ->where('expiration', '<', time())
                ->limit(self::CHUNK)
                ->delete();

            $deleted += $batch;
        } while ($batch > 0);

        return $deleted;
    }

    /**
     * @return array{int, int}
     */
    private function pruneFiles(): array
    {
        $root = config('cache.stores.file.path');

        if (! is_dir($root)) {
            return [0, 0];
        }

        $start = microtime(true);
        $timeBudget = max(0, (int) $this->option('time-budget'));
        $now = time();

        $dirs = $this->option('dir') ?: array_map('basename', File::directories($root));

        $deleted = 0;
        $kept = 0;

        foreach ($dirs as $dir) {
            $path = $root . DIRECTORY_SEPARATOR . basename($dir);

            if (! is_dir($path)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $expires = @file_get_contents($file->getPathname(), false, null, 0, 10);

                // Rozpísaný alebo cudzí súbor nechať tak.
                if ($expires === false || ! ctype_digit($expires) || (int) $expires >= $now) {
                    $kept++;

                    continue;
                }

                if (@unlink($file->getPathname())) {
                    $deleted++;
                }
            }

            if ($timeBudget > 0 && microtime(true) - $start > $timeBudget) {
                break;
            }
        }

        return [$deleted, $kept];
    }
}
