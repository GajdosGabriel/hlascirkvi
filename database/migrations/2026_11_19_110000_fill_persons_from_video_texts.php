<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Jednorazové priradenie osôb k už stiahnutým videám.
 *
 * Mená boli vytiahnuté z názvov a popisov videí (bez AI API) a ručne
 * prečistené; skloňované tvary sú zlúčené do nominatívu. Príspevky sa
 * párujú podľa video_id, kanály podľa id aj slugu, takže na produkcii sa
 * zapíše len to, čo tam naozaj existuje. Novšie videá rieši bežný import.
 *
 * Zdroj väzby: title (meno v názve), description (meno v popise),
 * canal (video z osobného kanála osoby bez iného mena v názve).
 */
return new class extends Migration
{
    private const CONFIDENCE = ['title' => 90, 'description' => 70, 'canal' => 60];

    private const SOURCE = ['t' => 'title', 'd' => 'description', 'c' => 'canal'];

    public function up(): void
    {
        $persons = json_decode(file_get_contents(__DIR__.'/../data/persons-2026-11-19.json'), true, 512, JSON_THROW_ON_ERROR);

        $videoIds = [];
        foreach ($persons as $person) {
            foreach (array_keys($person['posts']) as $videoId) {
                $videoIds[(string) $videoId] = true;
            }
        }
        $postIds = [];
        foreach (array_chunk(array_keys($videoIds), 1000) as $chunk) {
            foreach (DB::table('posts')->whereIn('video_id', $chunk)->whereNull('deleted_at')->pluck('id', 'video_id') as $videoId => $id) {
                $postIds[$videoId] = $id;
            }
        }
        $canals = DB::table('canals')->whereNull('deleted_at')->pluck('slug', 'id');
        $usedSlugs = DB::table('persons')->whereNotNull('slug')->pluck('slug')->flip()->all();
        $now = now();

        DB::transaction(function () use ($persons, $postIds, $canals, &$usedSlugs, $now) {
            foreach ($persons as $person) {
                $normalized = $this->normalize($person['name']);
                $ownCanal = $person['own_canal'] && ($canals[$person['own_canal']['id']] ?? null) === $person['own_canal']['slug']
                    ? $person['own_canal']['id'] : null;
                $hasCanal = $ownCanal !== null || array_filter($person['canals'], fn ($c) => ($canals[$c['id']] ?? null) === $c['slug']) !== [];
                $hasPost = array_intersect_key($person['posts'], $postIds) !== [];
                // Osobu bez jediného videa či kanála v tejto databáze nezakladáme.
                if (! $hasCanal && ! $hasPost) {
                    continue;
                }

                $personId = DB::table('persons')->where('normalized_name', $normalized)->whereNull('deleted_at')->value('id');
                if ($personId === null) {
                    $personId = DB::table('persons')->insertGetId([
                        'first_name' => $person['first_name'],
                        'last_name' => $person['last_name'],
                        'name' => $person['name'],
                        'normalized_name' => $normalized,
                        'slug' => $this->slug($person['name'], $usedSlugs),
                        'canal_id' => $ownCanal,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } elseif ($ownCanal !== null) {
                    DB::table('persons')->where('id', $personId)->whereNull('canal_id')->update(['canal_id' => $ownCanal]);
                }

                $aliases = [];
                foreach ($person['aliases'] as $alias) {
                    $key = $this->normalize($alias);
                    if ($key !== $normalized && ! isset($aliases[$key])) {
                        $aliases[$key] = ['person_id' => $personId, 'name' => mb_substr($alias, 0, 255), 'normalized_name' => $key, 'created_at' => $now, 'updated_at' => $now];
                    }
                }
                if ($aliases !== []) {
                    DB::table('person_aliases')->insertOrIgnore(array_values($aliases));
                }

                $canalRows = [];
                foreach ($person['canals'] as $canal) {
                    if (($canals[$canal['id']] ?? null) === $canal['slug']) {
                        $canalRows[] = ['canal_id' => $canal['id'], 'person_id' => $personId, 'source' => 'derived', 'posts_count' => $canal['posts'], 'created_at' => $now, 'updated_at' => $now];
                    }
                }
                if ($canalRows !== []) {
                    DB::table('canal_person')->insertOrIgnore($canalRows);
                }

                $links = [];
                foreach ($person['posts'] as $videoId => $where) {
                    if (! isset($postIds[$videoId])) {
                        continue;
                    }
                    $source = self::SOURCE[$where];
                    $links[] = [
                        'person_id' => $personId,
                        'post_id' => $postIds[$videoId],
                        'source' => $source,
                        'matched_text' => null,
                        'confidence' => self::CONFIDENCE[$source],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                foreach (array_chunk($links, 500) as $chunk) {
                    DB::table('person_post')->insertOrIgnore($chunk);
                }
            }
        });
    }

    public function down(): void
    {
        // Dáta z tejto migrácie (neoverené osoby a ich automatické väzby).
        DB::table('person_post')->whereIn('source', array_values(self::SOURCE))->delete();
        DB::table('canal_person')->where('source', 'derived')->delete();
        DB::table('persons')->whereNull('verified_at')
            ->whereNotExists(fn ($q) => $q->from('person_post')->whereColumn('person_post.person_id', 'persons.id'))
            ->whereNotExists(fn ($q) => $q->from('canal_person')->whereColumn('canal_person.person_id', 'persons.id'))
            ->delete();
    }

    private function normalize(string $name): string
    {
        return Str::lower(Str::ascii(trim(preg_replace('/\s+/u', ' ', $name))));
    }

    private function slug(string $name, array &$used): string
    {
        $base = Str::slug($name) ?: 'osoba';
        $slug = $base;
        for ($i = 2; isset($used[$slug]); $i++) {
            $slug = $base.'-'.$i;
        }
        $used[$slug] = true;

        return $slug;
    }
};
