<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Persons\CanalPersonSync;
use App\Services\Persons\PersonDetector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Rozpozná osoby v nových príspevkoch (persons_checked_at je prázdne) a
 * prepočíta, kto v ktorom kanáli pôsobí.
 *
 * --recheck prejde pravidlami len názvy (bez popisu, AI a osoby osobného
 * kanála) všetkých príspevkov, ktoré osobu nemajú — keď pribudnú osoby alebo
 * pravidlá, staré videá sa tak priradia aj k menám, ktoré pri prvej kontrole
 * ešte neboli známe. Popisy starých videí prešla ručná kontrola.
 */
class PersonsDetect extends Command
{
    protected $signature = 'persons:detect
        {--limit= : Najviac príspevkov v jednom behu}
        {--no-ai : Len pravidlá, bez AI}
        {--recheck : Znova prejsť názvy všetkých príspevkov bez osoby (len pravidlá)}';

    protected $description = 'Rozpozná osoby vo videách, ktoré ešte neprešli kontrolou';

    public function handle(PersonDetector $detector, CanalPersonSync $sync): int
    {
        if ($this->option('recheck')) {
            return $this->recheck($detector, $sync);
        }

        $limit = (int) ($this->option('limit') ?: config('persons.batch', 200));
        $posts = Post::query()
            ->without(['favorites', 'images', 'canal'])
            ->whereNull('persons_checked_at')
            ->latest('id')
            ->limit(max(1, $limit))
            ->get(['id', 'canal_id', 'title', 'body']);

        $linked = 0;
        foreach ($posts as $post) {
            try {
                $linked += count($detector->detect($post, ! $this->option('no-ai')));
            } catch (Throwable $e) {
                report($e);
            }
        }

        $canals = $sync->rebuild();
        $this->info("Príspevkov: {$posts->count()}, nových väzieb: {$linked}, väzieb osoba–kanál: {$canals}.");

        return self::SUCCESS;
    }

    protected function recheck(PersonDetector $detector, CanalPersonSync $sync): int
    {
        $checked = 0;
        $linked = 0;

        Post::query()
            ->without(['favorites', 'images', 'canal'])
            ->whereNotExists(fn ($q) => $q->from('person_post')->whereColumn('person_post.post_id', 'posts.id'))
            ->select(['id', 'canal_id', 'title', 'body'])
            ->chunkById(500, function ($posts) use ($detector, &$checked, &$linked) {
                foreach ($posts as $post) {
                    $checked++;
                    try {
                        $linked += count($detector->detect($post, false, false, false)) > 0 ? 1 : 0;
                    } catch (Throwable $e) {
                        report($e);
                    }
                }
            });

        $canals = $sync->rebuild();
        $this->info("Prejdených príspevkov bez osoby: {$checked}, teraz s osobou: {$linked}, väzieb osoba–kanál: {$canals}.");

        return self::SUCCESS;
    }
}
