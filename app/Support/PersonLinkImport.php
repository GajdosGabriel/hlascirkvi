<?php

namespace App\Support;

use App\Models\Person;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Zápis ručne prejdených dávok „kto vystupuje vo videu" z dátových migrácií.
 *
 * Súbor: {"persons": [{name, first_name, last_name, aliases[]}],
 *         "links": [{v: video_id, p: meno osoby, s: title|description|review|canal}]}
 *
 * Osoby sa hľadajú podľa normalizovaného mena aj iných podôb, príspevky podľa
 * video_id. Čo v databáze nie je, sa preskočí; osoba bez jediného videa sa
 * nezakladá. Opakované spustenie nič nezdvojí.
 */
class PersonLinkImport
{
    public const CONFIDENCE = ['title' => 90, 'review' => 85, 'description' => 70, 'canal' => 60];

    public static function run(string $path): void
    {
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $postIds = [];
        foreach (array_chunk(array_values(array_unique(array_column($data['links'], 'v'))), 1000) as $chunk) {
            foreach (DB::table('posts')->whereIn('video_id', $chunk)->whereNull('deleted_at')->pluck('id', 'video_id') as $videoId => $id) {
                $postIds[$videoId] = $id;
            }
        }

        $byPerson = [];
        foreach ($data['links'] as $link) {
            if (isset($postIds[$link['v']])) {
                $byPerson[Person::normalize($link['p'])][] = $link;
            }
        }
        $definitions = [];
        foreach ($data['persons'] as $person) {
            $definitions[Person::normalize($person['name'])] = $person;
        }

        $now = now();
        DB::transaction(function () use ($byPerson, $definitions, $postIds, $now) {
            $usedSlugs = DB::table('persons')->whereNotNull('slug')->pluck('slug')->flip()->all();

            foreach ($byPerson as $normalized => $links) {
                $personId = self::find($normalized);
                $definition = $definitions[$normalized] ?? null;

                if ($personId === null) {
                    $name = $definition['name'] ?? $links[0]['p'];
                    $personId = DB::table('persons')->insertGetId([
                        'first_name' => $definition['first_name'] ?? null,
                        'last_name' => $definition['last_name'] ?? null,
                        'name' => $name,
                        'normalized_name' => $normalized,
                        'slug' => self::slug($name, $usedSlugs),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $aliases = [];
                foreach ($definition['aliases'] ?? [] as $alias) {
                    $key = Person::normalize($alias);
                    if ($key !== $normalized) {
                        $aliases[$key] = ['person_id' => $personId, 'name' => mb_substr($alias, 0, 255), 'normalized_name' => $key, 'created_at' => $now, 'updated_at' => $now];
                    }
                }
                if ($aliases !== []) {
                    DB::table('person_aliases')->insertOrIgnore(array_values($aliases));
                }

                $rows = [];
                foreach ($links as $link) {
                    $rows[$postIds[$link['v']]] = [
                        'person_id' => $personId,
                        'post_id' => $postIds[$link['v']],
                        'source' => $link['s'],
                        'matched_text' => null,
                        'confidence' => self::CONFIDENCE[$link['s']] ?? 70,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                foreach (array_chunk(array_values($rows), 500) as $chunk) {
                    DB::table('person_post')->insertOrIgnore($chunk);
                }
            }
        });
    }

    /** Existujúca osoba podľa mena alebo inej podoby mena. */
    private static function find(string $normalized): ?int
    {
        return DB::table('persons')->where('normalized_name', $normalized)->whereNull('deleted_at')->value('id')
            ?? DB::table('person_aliases')->join('persons', 'persons.id', '=', 'person_aliases.person_id')
                ->where('person_aliases.normalized_name', $normalized)->whereNull('persons.deleted_at')
                ->value('persons.id');
    }

    private static function slug(string $name, array &$used): string
    {
        $base = Str::slug($name) ?: 'osoba';
        $slug = $base;
        for ($i = 2; isset($used[$slug]); $i++) {
            $slug = $base.'-'.$i;
        }
        $used[$slug] = true;

        return $slug;
    }
}
