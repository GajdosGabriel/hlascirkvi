<?php

namespace App\Services;

use App\Enums\CanalIdentityMode;
use App\Models\Canal;
use App\Models\CanalEnrichment;
use App\Models\Setting;
use App\Notifications\Canals\CanalProfileCompleted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CanalProfileEnricher
{
    public const SETTING_ENABLED = 'ai_enrichment.enabled';

    public function __construct(private CanalProfileResearch $research, private PostSummarizer $budget) {}

    public function enabled(): bool
    {
        return (bool) Setting::get(self::SETTING_ENABLED, true);
    }

    public function missing(Canal $canal): array
    {
        return array_values(array_filter(CanalProfileResearch::FIELDS,
            fn ($field) => trim(html_entity_decode(strip_tags((string) $canal->$field))) === ''));
    }

    public function run(Canal $canal): void
    {
        Cache::lock('canal-enrichment:'.$canal->id, 300)->get(function () use ($canal) {
            $canal = $canal->fresh();
            if (! $canal || $canal->identity_mode !== CanalIdentityMode::Organization || ! $canal->published
                || $canal->created_at->gt(now()->subHours(2))) {
                return;
            }
            $audit = CanalEnrichment::firstOrCreate(['canal_id' => $canal->id]);
            if ($audit->completed_at) {
                $this->notify($canal, $audit);

                return;
            }
            if (! $this->enabled() || ! $this->budget->isConfigured() || $this->budget->budgetExhausted()
                || $audit->attempts >= 3 || $audit->retry_at?->isFuture()) {
                return;
            }
            if (($missing = $this->missing($canal)) === []) {
                $audit->update(['completed_at' => now(), 'notification_completed_at' => now()]);

                return;
            }
            $audit->update(['attempts' => $audit->attempts + 1, 'retry_at' => now()->addHours(6)]);
            try {
                $found = $this->research->research($canal, $missing);
                DB::transaction(function () use ($canal, $audit, $found) {
                    // Re-read under a row lock: users may have edited the profile during the search.
                    $current = Canal::whereKey($canal->id)->lockForUpdate()->first();
                    $changes = [];
                    $evidence = [];
                    if ($current && $current->identity_mode === CanalIdentityMode::Organization && $current->published
                        && $current->title === $canal->title && $current->village_id === $canal->village_id
                        && $current->url_www === $canal->url_www && $current->youtube_channel === $canal->youtube_channel
                        && $current->denomination === $canal->denomination) {
                        foreach ($this->missing($current) as $field) {
                            if (isset($found[$field])) {
                                $changes[$field] = $found[$field]['value'];
                                $evidence[$field] = $found[$field];
                            }
                        }
                        // The legacy URL cast drops paths. Preserve verified organization subpages here.
                        $attributes = $changes;
                        unset($attributes['url_www']);
                        $current->forceFill($attributes);
                        if (isset($changes['url_www'])) {
                            $current->setRawAttributes(array_replace($current->getAttributes(), ['url_www' => $changes['url_www']]));
                        }
                        if ($changes !== []) {
                            $current->save();
                        }
                    }
                    $recipients = $changes && $current ? $current->users()->pluck('users.id')->all() : [];
                    $audit->update([
                        'completed_at' => now(), 'changes' => $changes, 'evidence' => $evidence,
                        'recipients' => $recipients, 'notified' => [],
                        'notification_completed_at' => $recipients === [] ? now() : null,
                    ]);
                });
                if ($fresh = $canal->fresh()) {
                    $this->notify($fresh, $audit->fresh());
                }
            } catch (Throwable $e) {
                Log::warning('Canal profile enrichment failed', ['canal_id' => $canal->id, 'error' => $e->getMessage()]);
            }
        });
    }

    private function notify(Canal $canal, CanalEnrichment $audit): void
    {
        if ($audit->notification_completed_at || ! $audit->changes) {
            return;
        }
        $notified = $audit->notified ?? [];
        foreach ($canal->users()->whereIn('users.id', array_diff($audit->recipients ?? [], $notified))->get() as $user) {
            if (! $user->hasVerifiedEmail() || $user->disabled) {
                continue;
            }
            try {
                $user->notify(new CanalProfileCompleted($canal, $audit->changes));
                $notified[] = $user->id;
                $audit->update(['notified' => $notified]);
            } catch (Throwable $e) {
                Log::warning('Canal profile notification failed', ['canal_id' => $canal->id, 'user_id' => $user->id]);

                return;
            }
        }
        $audit->update(['notification_completed_at' => now()]);
    }
}
