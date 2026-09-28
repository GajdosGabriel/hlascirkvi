<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Frozen reviewed data: no API requests, model events or notifications during deployment.
        $profiles = json_decode(file_get_contents(__DIR__.'/../data/canal-profiles-2026-09-28.json'), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($profiles) {
            foreach ($profiles as $profile) {
                $canal = DB::table('canals')->where('id', $profile['id'])
                    ->where('slug', $profile['slug'])->where('title', $profile['title'])
                    ->where('identity_mode', 'organization')->whereNull('deleted_at')
                    ->lockForUpdate()->first();
                if (! $canal) {
                    continue;
                }
                $changes = [];
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
                if (isset($profile['paragraphs']) && trim(html_entity_decode(strip_tags((string) $canal->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === '') {
                    $changes['description'] = implode("\n", array_map(
                        fn ($text) => '<p>'.htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p>',
                        $profile['paragraphs']
                    ));
                }
                // Coordinates must be a complete pair for the same street and municipality.
                if (isset($profile['coordinates']) && $canal->latitude === null && $canal->longitude === null
                    && ($changes['street'] ?? $canal->street) === $profile['coordinates']['street']
                    && (int) $canal->village_id === $profile['coordinates']['village_id']) {
                    $changes['latitude'] = $profile['coordinates']['latitude'];
                    $changes['longitude'] = $profile['coordinates']['longitude'];
                }
                if ($changes !== []) {
                    DB::table('canals')->where('id', $canal->id)->update($changes + ['updated_at' => now()]);
                }
            }
        });
    }

    public function down(): void
    {
        // Forward-only data migration: never erase later editorial changes.
    }
};
