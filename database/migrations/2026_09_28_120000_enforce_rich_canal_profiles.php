<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The reviewed snapshot is deliberately local so deployments never depend on a live API.
        $profiles = json_decode(
            file_get_contents(__DIR__.'/../data/canal-profiles-2026-09-28.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        DB::transaction(function () use ($profiles) {
            foreach ($profiles as $profile) {
                $paragraphs = $profile['paragraphs'] ?? [];
                $this->validateDescription($profile, $paragraphs);

                $canal = DB::table('canals')
                    ->where('id', $profile['id'])
                    ->where('slug', $profile['slug'])
                    ->where('title', $profile['title'])
                    ->where('identity_mode', 'organization')
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (! $canal) {
                    continue;
                }

                $changes = [
                    'description' => implode("\n", array_map(
                        fn (string $text) => '<p>'.htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p>',
                        $paragraphs
                    )),
                ];

                foreach ($profile['fields'] as $field => $value) {
                    if (! in_array($field, ['url_www', 'email', 'phone', 'street', 'psc'], true)) {
                        throw new RuntimeException('Unexpected profile field: '.$field);
                    }

                    if ($canal->{$field} === null || trim((string) $canal->{$field}) === '') {
                        $changes[$field] = $value;
                    }
                }

                if (isset($changes['phone']) && ! empty($canal->phone_numeric)
                    && (string) $canal->phone_numeric !== preg_replace('/\D/', '', $changes['phone'])) {
                    unset($changes['phone']);
                }

                if (isset($changes['phone']) && empty($canal->phone_numeric)) {
                    $changes['phone_numeric'] = preg_replace('/\D/', '', $changes['phone']);
                }

                // A point is accepted only when it belongs to the exact reviewed address.
                if (isset($profile['coordinates'])
                    && $canal->latitude === null && $canal->longitude === null
                    && ($changes['street'] ?? $canal->street) === $profile['coordinates']['street']
                    && (int) $canal->village_id === $profile['coordinates']['village_id']) {
                    $changes['latitude'] = $profile['coordinates']['latitude'];
                    $changes['longitude'] = $profile['coordinates']['longitude'];
                }

                DB::table('canals')->where('id', $canal->id)->update($changes + ['updated_at' => now()]);
            }
        });
    }

    private function validateDescription(array $profile, array $paragraphs): void
    {
        if (count($paragraphs) !== 3 || in_array('', array_map('trim', $paragraphs), true)) {
            throw new RuntimeException("Canal {$profile['id']} must have exactly three non-empty paragraphs.");
        }

        $sentenceCount = preg_match_all('/[.!?](?=\s|$)/u', implode(' ', $paragraphs));
        if ($sentenceCount < 10) {
            throw new RuntimeException("Canal {$profile['id']} must have at least ten sentences.");
        }
    }

    public function down(): void
    {
        // Forward-only data migration: never erase later editorial changes.
    }
};
