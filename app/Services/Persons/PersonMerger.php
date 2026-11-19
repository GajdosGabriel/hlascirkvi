<?php

namespace App\Services\Persons;

use App\Models\Person;
use Illuminate\Support\Facades\DB;

/**
 * Zlúči duplicitnú osobu do inej: videá, kanály a iné podoby mena prejdú
 * na cieľovú osobu, meno zlúčenej ostane ako iná podoba mena. Zlúčená osoba
 * sa zmaže natrvalo (jej slug sa tak uvoľní).
 */
class PersonMerger
{
    public function merge(Person $from, Person $into): void
    {
        if ($from->is($into)) {
            return;
        }

        DB::transaction(function () use ($from, $into) {
            $now = now();

            $links = DB::table('person_post')->where('person_id', $from->id)->get(['post_id', 'source', 'matched_text', 'confidence']);
            foreach ($links->chunk(500) as $chunk) {
                DB::table('person_post')->insertOrIgnore($chunk->map(fn ($link) => [
                    'person_id' => $into->id, 'post_id' => $link->post_id, 'source' => $link->source,
                    'matched_text' => $link->matched_text, 'confidence' => $link->confidence,
                    'created_at' => $now, 'updated_at' => $now,
                ])->all());
            }

            $canals = DB::table('canal_person')->where('person_id', $from->id)->get(['canal_id', 'role', 'source', 'posts_count']);
            DB::table('canal_person')->insertOrIgnore($canals->map(fn ($row) => [
                'canal_id' => $row->canal_id, 'person_id' => $into->id, 'role' => $row->role,
                'source' => $row->source, 'posts_count' => $row->posts_count,
                'created_at' => $now, 'updated_at' => $now,
            ])->all());

            $aliases = DB::table('person_aliases')->where('person_id', $from->id)->pluck('name')->push($from->name);
            DB::table('person_aliases')->insertOrIgnore($aliases
                ->map(fn ($name) => ['name' => $name, 'normalized_name' => Person::normalize($name)])
                ->reject(fn ($alias) => $alias['normalized_name'] === $into->normalized_name)
                ->unique('normalized_name')
                ->map(fn ($alias) => $alias + ['person_id' => $into->id, 'created_at' => $now, 'updated_at' => $now])
                ->values()->all());

            if ($into->canal_id === null && $from->canal_id !== null) {
                $into->forceFill(['canal_id' => $from->canal_id]);
            }
            $into->forceFill(['verified_at' => $into->verified_at ?? $from->verified_at])->save();

            $from->forceDelete();
        });
    }
}
