<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Dopĺňa width/height existujúcim obrázkom.
 *
 * Rozmery sa čítajú z hlavičky súboru, nič sa neprekódováva ani nesťahuje —
 * ide len o to, aby <img> vedel prehliadaču dopredu povedať pomer strán
 * a obsah pod obrázkom pri načítaní neodskočil.
 */
class ImageDimensions extends Command
{
    protected $signature = 'images:dimensions
        {--limit=0 : Spracovať najviac toľko záznamov (0 = všetky)}
        {--dry-run : Len vypísať, čo by sa stalo}';

    protected $description = 'Fill in width/height for images that are missing them';

    private const CHUNK = 500;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));
        $disk = Storage::disk(config('images.disk'));

        $query = Image::withTrashed()->whereNull('width');
        $total = $limit > 0 ? min($limit, $query->count()) : $query->count();

        if ($total === 0) {
            $this->info('Všetky obrázky už rozmery majú.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[nasucho] ' : '') . "Spracúvam {$total} záznamov.");
        $bar = $this->output->createProgressBar($total);

        $done = 0;
        $filled = 0;
        $missing = 0;

        // Stránkuje sa cez id, nie cez offset – zápis width by inak priebežne
        // menil výsledok dopytu a časť riadkov by sa preskočila.
        $lastId = 0;

        while ($done < $total) {
            $batch = Image::withTrashed()
                ->whereNull('width')
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit(min(self::CHUNK, $total - $done))
                ->get();

            if ($batch->isEmpty()) {
                break;
            }

            foreach ($batch as $image) {
                $lastId = $image->id;
                $done++;
                $bar->advance();

                if (! $disk->exists($image->url)) {
                    $missing++;
                    continue;
                }

                $info = @getimagesizefromstring($disk->get($image->url));

                if ($info === false) {
                    $missing++;
                    continue;
                }

                $filled++;

                if (! $dryRun) {
                    $image->forceFill(['width' => $info[0], 'height' => $info[1]])->saveQuietly();
                }
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->line("  doplnené: {$filled}");
        $this->line("  bez súboru alebo nečitateľné: {$missing}");

        if ($dryRun) {
            $this->comment('Nasucho – nič sa nezapísalo.');
        }

        return self::SUCCESS;
    }
}
