<?php

namespace App\Console\Commands;

use App\Models\PendingComment;
use App\Models\PendingPrayer;
use Illuminate\Console\Command;

/**
 * Jedna pripomienka modlitbám a komentárom z čakárne, ktoré ani po
 * REMIND_AFTER_DAYS nikto nepotvrdil („Vaša modlitba / váš komentár čaká na
 * zverejnenie"). Na jednu adresu odíde za každý druh len jeden e-mail, aj keď
 * ich čaká viac — potvrdenie aj tak zverejní všetky.
 */
class RemindPendingContributions extends Command
{
    protected $signature = 'pending:remind';

    protected $description = 'Send a single reminder to unconfirmed prayers and comments';

    public function handle(): int
    {
        $sent = 0;

        foreach ([PendingPrayer::class, PendingComment::class] as $model) {
            $model::dueForReminder()->chunkById(100, function ($rows) use ($model, &$sent) {
                foreach ($rows as $pending) {
                    // Iný riadok s rovnakou adresou mohol pripomienku už dostať
                    // v tomto behu (nižšie ho označíme).
                    if ($pending->fresh()?->reminded_at) {
                        continue;
                    }

                    $pending->sendReminder();
                    $model::where('email', $pending->email)->whereNull('reminded_at')
                        ->update(['reminded_at' => now()]);
                    $sent++;
                }
            });
        }

        $this->info(sprintf('Pripomienok odoslaných: %d', $sent));

        return self::SUCCESS;
    }
}
