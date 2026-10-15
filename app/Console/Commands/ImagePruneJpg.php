<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Zmaže menšie JPG varianty obrázkov, ktoré majú rovnaký WebP variant.
 *
 * StoreImage pôvodne ukladal každú šírku dvakrát (JPG aj WebP). Karty
 * a galéria však JPG potrebujú len pre prehliadač bez WebP, takže dnes
 * vzniká JPG už len v najväčšej šírke – pre stĺpec url (og:image, lightbox).
 * Tento príkaz dorovná staršie záznamy na ten istý tvar:
 *
 *  - variants.jpg ostane len s najväčšou šírkou,
 *  - thumb ukazuje na najmenší WebP namiesto najmenšieho JPG,
 *  - ostatné JPG súbory sa zmažú z disku.
 *
 * Záznam v DB sa prepíše skôr, než sa zmažú súbory. Keď beh spadne medzi
 * tým, ostanú na disku len siroty, nikdy nie odkaz na chýbajúci súbor.
 * Beh je opakovateľný – hotové záznamy už majú jediný JPG a preskočia sa.
 */
class ImagePruneJpg extends Command
{
    protected $signature = 'images:prune-jpg
        {--limit=0 : Spracovať najviac toľko záznamov (0 = všetky)}
        {--dry-run : Len vypísať, čo by sa stalo}';

    protected $description = 'Delete smaller JPG variants that have a WebP counterpart';

    private const CHUNK = 500;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));
        $disk = Storage::disk(config('images.disk'));

        $stat = ['pruned' => 0, 'files' => 0, 'bytes' => 0, 'skipped' => 0];
        $processed = 0;
        $lastId = 0;

        $this->info(($dryRun ? '[nasucho] ' : '') . 'Prechádzam obrázky s variantmi…');
        $bar = $this->output->createProgressBar(
            Image::withTrashed()->whereNotNull('variants')->count()
        );

        // Stránkuje sa cez id – zápis do variants by pri offsete posúval
        // výsledok dopytu a časť riadkov by sa preskočila.
        while (true) {
            $batch = Image::withTrashed()
                ->whereNotNull('variants')
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit(self::CHUNK)
                ->get();

            if ($batch->isEmpty()) {
                break;
            }

            foreach ($batch as $image) {
                $lastId = $image->id;
                $bar->advance();

                $jpg = $image->variants['jpg'] ?? [];
                $webp = $image->variants['webp'] ?? [];

                // Hotové alebo nový tvar – nie je čo mazať.
                if (count($jpg) <= 1) {
                    continue;
                }

                if ($limit > 0 && $processed >= $limit) {
                    break 2;
                }

                $processed++;

                krsort($jpg);
                krsort($webp);

                $keepWidth = array_key_first($jpg);
                $keep = $jpg[$keepWidth];
                $thumb = end($webp);
                $remove = array_values(array_diff($jpg, [$keep]));

                // Bez WebP náprotivku každej šírky by karta ostala bez obrázka
                // a pri url mimo najväčšieho JPG by sme zmazali niečo, na čo
                // záznam stále ukazuje. Také záznamy necháme na ručné riešenie.
                if (
                    $thumb === false
                    || $image->url !== $keep
                    || array_diff_key($jpg, $webp) !== []
                    || ! $disk->exists($thumb)
                ) {
                    $stat['skipped']++;
                    $this->newLine();
                    $this->warn("  preskočené #{$image->id}: chýba WebP variant alebo url nesedí s najväčším JPG");

                    continue;
                }

                foreach ($remove as $path) {
                    if ($disk->exists($path)) {
                        $stat['bytes'] += $disk->size($path);
                        $stat['files']++;
                    }
                }

                $stat['pruned']++;

                if ($dryRun) {
                    continue;
                }

                $variants = $image->variants;
                $variants['jpg'] = [$keepWidth => $keep];

                $image->forceFill(['variants' => $variants, 'thumb' => $thumb])->saveQuietly();

                $disk->delete($remove);
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->line("  upravené záznamy: {$stat['pruned']}");
        $this->line("  zmazané súbory:   {$stat['files']}");
        $this->line('  uvoľnené miesto:  ' . round($stat['bytes'] / 1048576, 1) . ' MB');
        $this->line("  preskočené:       {$stat['skipped']}");

        if ($dryRun) {
            $this->comment('Nasucho – nič sa nezapísalo ani nezmazalo.');
        }

        return self::SUCCESS;
    }
}
