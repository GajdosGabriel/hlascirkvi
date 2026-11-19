<?php

namespace App\Services\Persons;

use Illuminate\Support\Facades\DB;

/**
 * Odvodené väzby „osoba pôsobí v kanáli" (canal_person, zdroj derived).
 *
 * Osoba pôsobí v organizačnom kanáli, ak má v ňom aspoň canal_min_posts
 * videí. Ručne zadané väzby (iný zdroj) sa nemenia, vlastný kanál osoby sa
 * do zoznamu nezapisuje.
 */
class CanalPersonSync
{
    public function rebuild(): int
    {
        $min = max(1, (int) config('persons.canal_min_posts', 5));
        $now = now();

        $rows = DB::table('person_post')
            ->join('posts', 'posts.id', '=', 'person_post.post_id')
            ->join('canals', 'canals.id', '=', 'posts.canal_id')
            ->join('persons', 'persons.id', '=', 'person_post.person_id')
            ->whereNull('posts.deleted_at')
            ->whereNull('canals.deleted_at')
            ->whereNull('persons.deleted_at')
            ->where('canals.identity_mode', 'organization')
            ->where(fn ($q) => $q->whereNull('persons.canal_id')->orWhereColumn('persons.canal_id', '<>', 'canals.id'))
            ->groupBy('person_post.person_id', 'posts.canal_id')
            ->havingRaw('COUNT(*) >= ?', [$min])
            ->selectRaw('person_post.person_id, posts.canal_id, COUNT(*) as posts_count')
            ->get();

        return DB::transaction(function () use ($rows, $now) {
            $keep = [];
            foreach ($rows->chunk(500) as $chunk) {
                DB::table('canal_person')->upsert(
                    $chunk->map(fn ($row) => [
                        'canal_id' => $row->canal_id,
                        'person_id' => $row->person_id,
                        'source' => 'derived',
                        'posts_count' => $row->posts_count,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->values()->all(),
                    ['canal_id', 'person_id'],
                    // Ručnej väzbe sa aktualizuje len počet.
                    ['posts_count', 'updated_at']
                );
                foreach ($chunk as $row) {
                    $keep[$row->person_id.'-'.$row->canal_id] = true;
                }
            }

            // Odvodené väzby, ktoré už podmienku nespĺňajú.
            $stale = DB::table('canal_person')->where('source', 'derived')->get(['id', 'person_id', 'canal_id'])
                ->reject(fn ($row) => isset($keep[$row->person_id.'-'.$row->canal_id]))
                ->pluck('id');
            foreach ($stale->chunk(500) as $ids) {
                DB::table('canal_person')->whereIn('id', $ids)->delete();
            }

            return count($keep);
        });
    }
}
