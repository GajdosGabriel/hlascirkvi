<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\Post;
use App\Services\Images\StoreImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Nahrádza staré náhľady videí novým, ostrejším variantom stiahnutým znova
 * z YouTube.
 *
 * Prekódovať existujúci súbor by nepomohlo — je 360 px široký a vznikol
 * roztiahnutím z 320 px, tá informácia v ňom nie je. Zdroj je ale stále
 * dosiahnuteľný: video_id je v tabuľke posts a náhľady sedia na CDN
 * i.ytimg.com, kde nie je kvóta ani potreba API kľúča. Na vzorke 75 videí
 * malo 1280 px náhľad 78 % článkov, ďalších 12 % aspoň 480 px.
 *
 * Beh je prerušiteľný: berú sa len obrázky bez vyplneného stĺpca variants,
 * takže opakované spustenie plynule pokračuje.
 */
class ImageRefetch extends Command
{
    protected $signature = 'images:refetch
        {--year=* : Obmedziť na roky vzniku obrázka, napr. --year=2025 --year=2026}
        {--org= : Obmedziť na jednu organizáciu}
        {--limit=0 : Spracovať najviac toľko obrázkov (0 = všetky)}
        {--sleep=0 : Pauza medzi obrázkami v milisekundách}
        {--dry-run : Len zistiť dostupnosť, nič nesťahovať ani nemazať}';

    protected $description = 'Re-fetch YouTube thumbnails in full resolution for old images';

    /**
     * Od najväčšieho. maxresdefault na starších videách nebýva, hqdefault
     * (480 px) je stále lepší než dnešných 360.
     */
    private const CANDIDATES = ['maxresdefault', 'hqdefault'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));
        $sleep = max(0, (int) $this->option('sleep')) * 1000;

        $query = $this->candidates();
        $total = $limit > 0 ? min($limit, (clone $query)->count()) : (clone $query)->count();

        if ($total === 0) {
            $this->info('Nie je čo prepočítať.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[nasucho] ' : '') . "Na spracovanie: {$total} obrázkov.");
        $bar = $this->output->createProgressBar($total);

        $stat = ['maxres' => 0, 'hq' => 0, 'nedostupne' => 0, 'chyba' => 0, 'preskocene' => 0];
        $done = 0;
        $lastId = 0;

        // Stránkuje sa cez id — zápis nového obrázka a zmazanie starého menia
        // výsledok dopytu, takže offset by riadky preskakoval.
        while ($done < $total) {
            $batch = (clone $query)->where('images.id', '>', $lastId)
                ->orderBy('images.id')
                ->limit(min(200, $total - $done))
                ->get();

            if ($batch->isEmpty()) {
                break;
            }

            foreach ($batch as $image) {
                $lastId = $image->id;
                $done++;
                $bar->advance();

                $this->process($image, $dryRun, $stat);

                if ($sleep > 0) {
                    usleep($sleep);
                }

                if ($done >= $total) {
                    break;
                }
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->line("  nahradené z maxresdefault (1280 px): {$stat['maxres']}");
        $this->line("  nahradené z hqdefault (480 px):      {$stat['hq']}");
        $this->line("  náhľad na YouTube už nie je:         {$stat['nedostupne']}");
        $this->line("  preskočené (post má viac obrázkov):  {$stat['preskocene']}");
        $this->line("  chyby:                               {$stat['chyba']}");

        if ($dryRun) {
            $this->comment('Nasucho – nič sa nestiahlo, nezapísalo ani nezmazalo.');
        }

        return self::SUCCESS;
    }

    /**
     * @param array<string, int> $stat
     */
    private function process(Image $image, bool $dryRun, array &$stat): void
    {
        $post = Post::find($image->fileable_id);

        if (! $post || blank($post->video_id)) {
            $stat['chyba']++;

            return;
        }

        // Poistka: nahradiť sa dá len tam, kde je jednoznačné čo za čo.
        if ($post->images()->count() > 1) {
            $stat['preskocene']++;

            return;
        }

        foreach (self::CANDIDATES as $candidate) {
            $url = "https://i.ytimg.com/vi/{$post->video_id}/{$candidate}.jpg";

            if (! $this->available($url)) {
                continue;
            }

            $key = $candidate === 'maxresdefault' ? 'maxres' : 'hq';

            if ($dryRun) {
                $stat[$key]++;

                return;
            }

            try {
                $new = StoreImage::for($post)->fromUrl($url);
            } catch (Throwable $e) {
                $this->newLine();
                $this->warn("post {$post->id}: {$e->getMessage()}");
                $stat['chyba']++;

                return;
            }

            // Starý záznam ide preč až keď nový stojí; súbory po ňom upratuje
            // ImageObserver. Príznak hlavného obrázka sa prenáša, aby sa
            // výmenou nič nepresunulo.
            $new->forceFill(['is_primary' => $image->is_primary ?? true])->saveQuietly();
            $image->forceDelete();

            $stat[$key]++;

            return;
        }

        $stat['nedostupne']++;
    }

    private function available(string $url): bool
    {
        try {
            return Http::timeout(8)->head($url)->successful();
        } catch (Throwable) {
            return false;
        }
    }

    private function candidates()
    {
        $query = Image::query()
            ->join('posts', 'posts.id', '=', 'images.fileable_id')
            ->where('images.fileable_type', 'like', '%Post')
            ->whereNull('images.variants')
            ->whereNotNull('posts.video_id')
            ->where('posts.video_id', '<>', '')
            ->select('images.*');

        if ($years = array_filter((array) $this->option('year'))) {
            $query->whereIn(\DB::raw('YEAR(images.created_at)'), $years);
        }

        if ($org = $this->option('org')) {
            $query->where('posts.organization_id', $org);
        }

        return $query;
    }
}
