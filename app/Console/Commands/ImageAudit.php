<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Prehľad o stave obrázkov: čo patrí zmazaným modelom, čo nemá súbor
 * na disku a koľko z archívu ešte čaká na prepočet.
 *
 * Mazanie je za samostatným prepínačom a pýta si potvrdenie — 28 % záznamov
 * visí na modeli App\Models\Event, ktorý už v kóde neexistuje, a to je príliš
 * veľa na to, aby o tom rozhodol príkaz sám.
 */
class ImageAudit extends Command
{
    protected $signature = 'images:audit
        {--purge-orphans : Definitívne zmazať záznamy patriace neexistujúcim modelom}
        {--sample=200 : Koľko záznamov overiť na disku pri odhade}';

    protected $description = 'Report on image rows: dead owners, missing files, pending re-fetch';

    public function handle(): int
    {
        $this->owners();
        $this->newLine();
        $this->formats();
        $this->newLine();
        $this->files();

        if ($this->option('purge-orphans')) {
            $this->newLine();
            $this->purge();
        }

        return self::SUCCESS;
    }

    private function owners(): void
    {
        $this->info('Vlastníci');

        $rows = Image::withTrashed()
            ->selectRaw('fileable_type, COUNT(*) n')
            ->groupBy('fileable_type')
            ->orderByDesc('n')
            ->get();

        $this->table(
            ['model', 'záznamov', 'trieda existuje'],
            $rows->map(fn ($r) => [
                $r->fileable_type,
                number_format($r->n, 0, ',', ' '),
                class_exists($r->fileable_type) ? 'áno' : 'NIE',
            ])
        );

        $dead = $rows->reject(fn ($r) => class_exists($r->fileable_type));

        if ($dead->isNotEmpty()) {
            $this->warn(
                'Sirôt po zmazaných modeloch: ' . number_format($dead->sum('n'), 0, ',', ' ')
                . ' — zmazať sa dajú prepínačom --purge-orphans.'
            );
        }
    }

    private function formats(): void
    {
        $this->info('Formát');

        $novy = Image::withTrashed()->whereNotNull('variants')->count();
        $stary = Image::withTrashed()->whereNull('variants')->count();
        $bezRozmerov = Image::withTrashed()->whereNull('width')->count();

        $this->table(['stav', 'záznamov'], [
            ['nový formát (varianty + WebP)', number_format($novy, 0, ',', ' ')],
            ['starý formát, čaká na images:refetch', number_format($stary, 0, ',', ' ')],
            ['bez rozmerov, čaká na images:dimensions', number_format($bezRozmerov, 0, ',', ' ')],
        ]);
    }

    private function files(): void
    {
        $sample = max(1, (int) $this->option('sample'));
        $disk = Storage::disk(config('images.disk'));

        $rows = Image::withTrashed()->inRandomOrder()->limit($sample)->get(['url']);
        $chybaju = $rows->reject(fn ($i) => $disk->exists($i->url))->count();

        $this->info('Súbory na disku');
        $this->line("  vzorka {$rows->count()} záznamov, bez súboru: {$chybaju}");

        if ($chybaju > 0) {
            $podiel = round($chybaju / max(1, $rows->count()) * 100, 1);
            $this->warn("  odhadom {$podiel} % záznamov ukazuje na neexistujúci súbor");
        }
    }

    private function purge(): void
    {
        $dead = Image::withTrashed()
            ->selectRaw('fileable_type, COUNT(*) n')
            ->groupBy('fileable_type')
            ->get()
            ->reject(fn ($r) => class_exists($r->fileable_type));

        if ($dead->isEmpty()) {
            $this->info('Žiadne siroty na zmazanie.');

            return;
        }

        $types = $dead->pluck('fileable_type')->all();
        $count = $dead->sum('n');

        $this->warn('Zmaže sa ' . number_format($count, 0, ',', ' ') . ' záznamov (' . implode(', ', $types) . ') vrátane súborov na disku.');

        if (! $this->confirm('Naozaj pokračovať? Túto operáciu nejde vrátiť.', false)) {
            $this->info('Zrušené, nič sa nezmazalo.');

            return;
        }

        $bar = $this->output->createProgressBar($count);
        $zmazane = 0;

        Image::withTrashed()->whereIn('fileable_type', $types)->chunkById(200, function ($chunk) use ($bar, &$zmazane) {
            foreach ($chunk as $image) {
                $image->forceDelete();
                $zmazane++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Zmazaných záznamov: {$zmazane}");
    }
}
