<?php

namespace App\Console\Commands;

use App\Services\GuestReplier;
use Illuminate\Console\Command;

class CommentsReplyToGuests extends Command
{
    protected $signature = 'comments:reply-to-guests
                            {--limit=20 : Koľko vlákien spracovať}';

    protected $description = 'Po 3 hodinách zareaguje (OpenAI) na odpovede hosťom z YouTube, ktorí na webe odpovedať nemôžu';

    public function handle(GuestReplier $replier): int
    {
        if (! $replier->isConfigured()) {
            $this->warn('Chýba OPENAI_API_KEY, odpovede sa nevytvárajú.');

            return self::SUCCESS;
        }

        $done = 0;

        foreach ($replier->dueThreads((int) $this->option('limit')) as $parentId) {
            // Rovnaký mesačný limit ako zhrnutia (/admin/ai).
            if ($replier->budgetExhausted()) {
                $this->warn('Mesačný limit na AI je vyčerpaný.');
                break;
            }

            if ($replier->replyToThread($parentId)) {
                $done++;
            }
        }

        $this->info("Odpovede za hostí: {$done}.");

        return self::SUCCESS;
    }
}
