<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostSummarizer;
use Illuminate\Console\Command;

class PostSummarize extends Command
{
    protected $signature = 'posts:summarize
                            {--limit= : Koľko príspevkov spracovať (predvolene veľkosť dávky z administrácie)}
                            {--post= : Spracovať len tento príspevok (ID), aj keď už zhrnutie má}
                            {--force : Spustiť aj keď sú zhrnutia v administrácii vypnuté}';

    protected $description = 'Vytvorí krátke zhrnutia dlhých popisov príspevkov (OpenAI)';

    public function handle(PostSummarizer $summarizer): int
    {
        if (! $summarizer->isConfigured()) {
            $this->warn('Chýba OPENAI_API_KEY, zhrnutia sa nevytvárajú.');

            return self::SUCCESS;
        }

        // Plánovač spúšťa príkaz každú hodinu; či sa naozaj minú kredity,
        // rozhoduje vypínač v administrácii (/admin/ai).
        $forced = $this->option('force') || $this->option('post');

        if (! $forced && ! $summarizer->enabled()) {
            $this->info('AI zhrnutia sú v administrácii vypnuté.');

            return self::SUCCESS;
        }

        $query = Post::query()->without(['favorites', 'images', 'canal']);

        if ($id = $this->option('post')) {
            $query->whereKey($id);
        } else {
            $query->published()
                ->whereNull('summary_generated_at')
                ->whereNotNull('body')
                ->latest('id')
                ->limit($this->option('limit') ? (int) $this->option('limit') : $summarizer->batchSize());
        }

        $done = 0;

        foreach ($query->get() as $post) {
            // Limit sa kontroluje pred každým volaním, nie raz za dávku —
            // dávka by ho inak mohla prekročiť o celú svoju cenu.
            if ($summarizer->budgetExhausted()) {
                $this->warn('Mesačný limit na AI zhrnutia je vyčerpaný.');
                break;
            }

            if ($summarizer->summarizeAndStore($post) !== null) {
                $done++;
            }
        }

        $this->info("Hotové zhrnutia: {$done}.");

        return self::SUCCESS;
    }
}
