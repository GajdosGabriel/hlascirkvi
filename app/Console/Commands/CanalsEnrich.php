<?php

namespace App\Console\Commands;

use App\Enums\CanalIdentityMode;
use App\Models\Canal;
use App\Models\CanalEnrichment;
use App\Services\CanalProfileEnricher;
use Illuminate\Console\Command;

class CanalsEnrich extends Command
{
    protected $signature = 'canals:enrich {--limit=5 : Počet profilov v dávke}';

    protected $description = 'Doplní overiteľné chýbajúce údaje organizačných kanálov starších než dve hodiny';

    public function handle(CanalProfileEnricher $enricher): int
    {
        $limit = max(1, min(50, (int) $this->option('limit')));
        // Pending mail is retried independently of API availability and budget.
        $pending = CanalEnrichment::whereNotNull('completed_at')->whereNull('notification_completed_at')
            ->orderBy('updated_at')->limit($limit)->pluck('canal_id');
        foreach (Canal::whereIn('id', $pending)->get() as $canal) {
            $enricher->run($canal);
        }
        if (! $enricher->enabled()) {
            return self::SUCCESS;
        }
        $canals = Canal::where('identity_mode', CanalIdentityMode::Organization->value)->whereNotNull('published')
            ->where('created_at', '<=', now()->subHours(2))
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('canal_enrichments')
                    ->whereColumn('canal_enrichments.canal_id', 'canals.id')
                    ->where(fn ($q) => $q->whereNotNull('completed_at')->orWhere('attempts', '>=', 3)->orWhere('retry_at', '>', now()));
            })
            ->orderBy('id')->limit($limit)->get();
        foreach ($canals as $canal) {
            $enricher->run($canal);
        }
        $this->info('Kontrola organizačných profilov dokončená.');

        return self::SUCCESS;
    }
}
