<?php

namespace App\Console\Commands;

use App\Enums\CanalIdentityMode;
use App\Models\Canal;
use App\Models\CanalEnrichment;
use App\Services\CanalProfileEnricher;
use App\Services\CanalProfileResearch;
use Illuminate\Console\Command;

class CanalsEnrich extends Command
{
    protected $signature = 'canals:enrich {--limit=5 : Počet profilov v dávke}
        {--canal=* : Spracovať iba zadané ID kanálov}
        {--retry-empty : Znovu preveriť dokončené kanály bez doplnenia}';

    protected $description = 'Doplní overiteľné chýbajúce údaje organizačných kanálov starších než dve hodiny';

    public function handle(CanalProfileEnricher $enricher): int
    {
        $limit = max(1, min(50, (int) $this->option('limit')));
        $ids = $this->option('canal');
        if (array_filter($ids, fn ($id) => ! ctype_digit((string) $id) || (int) $id < 1)) {
            $this->error('ID kanálov musia byť kladné celé čísla.');

            return self::FAILURE;
        }
        // Pending mail is retried independently of API availability and budget.
        $pending = CanalEnrichment::whereNotNull('completed_at')->whereNull('notification_completed_at')
            ->when($ids, fn ($q) => $q->whereIn('canal_id', $ids))
            ->orderBy('updated_at')->limit($limit)->pluck('canal_id');
        foreach (Canal::whereIn('id', $pending)->get() as $canal) {
            $enricher->run($canal);
        }
        if (! $enricher->enabled()) {
            return self::SUCCESS;
        }
        $this->info('Model dopĺňania: '.config('openai.enrichment_model'));
        if ($this->option('retry-empty')) {
            $audits = CanalEnrichment::whereNotNull('completed_at')
                ->where(fn ($q) => $q->whereNull('changes')->orWhereJsonLength('changes', 0))
                ->whereHas('canal', function ($q) {
                    $q->where('identity_mode', CanalIdentityMode::Organization->value)
                        ->whereNotNull('published')->where('created_at', '<=', now()->subHours(2))
                        ->where(function ($q) {
                            foreach (CanalProfileResearch::FIELDS as $field) {
                                $q->orWhereNull($field)->orWhere($field, '');
                            }
                        });
                })
                ->when($ids, fn ($q) => $q->whereIn('canal_id', $ids))
                ->orderBy('updated_at')->limit($limit)->get();
            foreach ($audits as $audit) {
                if ($canal = $audit->canal()->first()) {
                    $enricher->run($canal, true);
                }
            }

            return self::SUCCESS;
        }
        $canals = Canal::where('identity_mode', CanalIdentityMode::Organization->value)->whereNotNull('published')
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
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
