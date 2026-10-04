<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // Vlastníka preberáme zo vzorového seminára, nie z lokálnych ID.
            $source = DB::table('seminars')
                ->where('title', 'Bratislavské Hanusove dni 2022')
                ->whereNull('deleted_at')->first();

            if ($source === null) {
                return;
            }

            $postsByYear = [2023 => [], 2024 => [], 2025 => [], 2026 => []];
            DB::table('posts')->select(['id', 'title'])->whereNull('deleted_at')->whereNotNull('video_id')
                ->where('video_id', '<>', '')->orderBy('id')
                ->chunkById(500, function ($posts) use (&$postsByYear) {
                    foreach ($posts as $post) {
                        $title = Str::lower(Str::ascii($post->title));
                        // Len explicitný ročník podujatia v názve; dátum publikácie
                        // ani zmienka v popise neurčujú ročník prednášky.
                        preg_match_all('/\b(?:bhd|bratislavske\s+hanusove\s+dni|bratislava\s+hanus\s+days)\s*[\x{2019}\x{0027}]?\s*(?:20)?(23|24|25|26)\b/u', $title, $matches);
                        $years = array_unique($matches[1]);
                        if (count($years) === 1) {
                            $postsByYear[2000 + (int) reset($years)][] = $post->id;
                        }
                    }
                }, 'id');

            $now = now();
            foreach ($postsByYear as $year => $postIds) {
                if ($year === 2026 && $postIds === []) {
                    continue;
                }

                $title = "Bratislavské Hanusove dni {$year}";
                $seminar = DB::table('seminars')->where('canal_id', $source->canal_id)
                    ->where('title', $title)->whereNull('deleted_at')->first();
                $seminarId = $seminar?->id ?? DB::table('seminars')->insertGetId([
                    'title' => $title,
                    'canal_id' => $source->canal_id,
                    'description' => null,
                    'youtube_playlist' => null,
                    'published' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach (array_chunk($postIds, 500) as $ids) {
                    DB::table('post_seminar')->insertOrIgnore(array_map(fn ($id) => [
                        'post_id' => $id, 'seminar_id' => $seminarId,
                        'created_at' => $now, 'updated_at' => $now,
                    ], $ids));
                    DB::table('posts')->whereIn('id', $ids)->where('section', '<>', 'seminar')
                        ->update(['section' => 'seminar', 'updated_at' => $now]);
                }
            }
        });
    }

    public function down(): void
    {
        // Dátové zaradenie ponecháme: spätné mazanie by mohlo odstrániť
        // aj neskoršie ručné úpravy a existujúce väzby seminárov.
    }
};
