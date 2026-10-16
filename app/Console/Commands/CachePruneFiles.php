<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Maže exspirované súbory súborovej cache (storage/framework/cache/data).
 *
 * FileStore exspirovanú položku zmaže až pri jej ďalšom čítaní. Kľúče, ktoré
 * sa už nikdy neprečítajú (počítadlá throttle pre každú IP, odpovede na
 * jednorazové dopyty), preto ostávajú na disku navždy. Pri návale botov
 * z tisícov IP tak vzniklo ~8 miliónov drobných súborov a 68 GB na disku —
 * súbor má pár bajtov, hosting však za každý účtuje celý blok.
 *
 * Každý súbor začína 10-cifernou unixovou časovou pečiatkou exspirácie
 * (9999999999 = navždy), takže stačí prečítať jeho začiatok.
 */
class CachePruneFiles extends Command
{
    protected $signature = 'cache:prune-files
        {--dir=* : Len tieto podadresáre (napr. --dir=7a --dir=ff)}
        {--time-budget=0 : Strop behu v sekundách (0 = bez stropu)}';

    protected $description = 'Delete expired entries of the file cache store';

    public function handle(): int
    {
        $root = config('cache.stores.file.path');
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

        $this->info(sprintf('CachePruneFiles: zmazaných %d, ponechaných %d', $deleted, $kept));

        return self::SUCCESS;
    }
}
