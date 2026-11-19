<?php

use App\Support\PersonLinkImport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ručne prejdená dávka 3: ďalšie videá bez priradenej osoby
 * (január 2024 – október 2021) a oprava chybných väzieb z osobného kanála. Rečník je určený z názvu a popisu videa.
 */
return new class extends Migration
{
    private const FILE = __DIR__.'/../data/persons-reviewed-batch-3.json';

    public function up(): void
    {
        PersonLinkImport::run(self::FILE);
    }

    public function down(): void
    {
        // Odstráni len väzby z tejto dávky; osoby bez videí zmaže.
        $data = json_decode(file_get_contents(self::FILE), true, 512, JSON_THROW_ON_ERROR);
        foreach (array_chunk($data['links'], 500) as $chunk) {
            foreach ($chunk as $link) {
                DB::table('person_post')
                    ->join('posts', 'posts.id', '=', 'person_post.post_id')
                    ->join('persons', 'persons.id', '=', 'person_post.person_id')
                    ->where('posts.video_id', $link['v'])
                    ->where('persons.normalized_name', App\Models\Person::normalize($link['p']))
                    ->where('person_post.source', $link['s'])
                    ->delete();
            }
        }
        DB::table('persons')->whereNull('verified_at')
            ->whereNotExists(fn ($q) => $q->from('person_post')->whereColumn('person_post.person_id', 'persons.id'))
            ->whereNotExists(fn ($q) => $q->from('canal_person')->whereColumn('canal_person.person_id', 'persons.id'))
            ->whereNull('canal_id')
            ->delete();
    }
};
