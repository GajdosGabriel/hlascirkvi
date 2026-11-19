<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Persons\CanalPersonSync;
use App\Services\Persons\PersonDetector;
use Illuminate\Console\Command;
use Throwable;

/**
 * Rozpozná osoby v nových príspevkoch (persons_checked_at je prázdne) a
 * prepočíta, kto v ktorom kanáli pôsobí.
 */
class PersonsDetect extends Command
{
    protected $signature = 'persons:detect
        {--limit= : Najviac príspevkov v jednom behu}
        {--no-ai : Len pravidlá, bez AI}';

    protected $description = 'Rozpozná osoby vo videách, ktoré ešte neprešli kontrolou';

    public function handle(PersonDetector $detector, CanalPersonSync $sync): int
    {
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
}
