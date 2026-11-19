<?php

use App\Services\Persons\CanalPersonSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Príprava na automatické rozpoznávanie osôb:
 * - posts.persons_checked_at: príspevok už prešiel kontrolou; doterajšie
 *   príspevky prešli ručnými dávkami, preto sa označia všetky,
 * - vlastný kanál osôb z ručných dávok (osobný kanál s rovnakým menom),
 * - prepočet „kto v ktorom kanáli pôsobí" pre všetky osoby.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'persons_checked_at')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->timestamp('persons_checked_at')->nullable();
                $table->index('persons_checked_at');
            });
        }

        DB::table('posts')->whereNull('persons_checked_at')->update(['persons_checked_at' => now()]);

        // Osobný kanál „Priezvisko Meno" alebo „Meno Priezvisko" patrí osobe
        // s tým menom, ak ho ešte nemá iná osoba.
        $normalize = fn ($text) => Str::lower(Str::ascii(trim(preg_replace('/\s+/u', ' ', (string) $text))));
        $taken = DB::table('persons')->whereNotNull('canal_id')->pluck('canal_id')->flip();
        $canals = DB::table('canals')->where('identity_mode', 'personal')->whereNull('deleted_at')->get(['id', 'title']);
        foreach ($canals as $canal) {
            if (isset($taken[$canal->id])) {
                continue;
            }
            $words = explode(' ', $normalize($canal->title));
            if (count($words) !== 2) {
                continue;
            }
            $person = DB::table('persons')->whereNull('deleted_at')->whereNull('canal_id')
                ->whereIn('normalized_name', [$words[0].' '.$words[1], $words[1].' '.$words[0]])
                ->first(['id']);
            if ($person) {
                DB::table('persons')->where('id', $person->id)->update(['canal_id' => $canal->id]);
                $taken[$canal->id] = true;
            }
        }

        $this->cleanUpPersons($normalize);

        app(CanalPersonSync::class)->rebuild();
    }

    /**
     * Omyly prvého automatického prechodu: názvy miest a postáv namiesto
     * osôb sa zmažú, skomolené mená sa zlúčia so správnou osobou.
     */
    private function cleanUpPersons(\Closure $normalize): void
    {
        $remove = ['Mikuláš Liptovský', 'Ondrej Liptovský', 'Pavla Aule', 'Nora Pšenčíka', 'Dávid Kráľ'];
        $merge = [
            'Nicol Girasoli' => 'Nicola Girasoli',
            'Peter Beň' => 'Peter Beňo',
            'Julo Slovák' => 'Július Slovák',
            'Jozef Jonáš' => 'Jonáš Maxim',
            'Martin Ralph' => 'Ralph Martin',
        ];

        foreach ($remove as $name) {
            $id = DB::table('persons')->where('normalized_name', $normalize($name))->value('id');
            if ($id !== null) {
                DB::table('person_post')->where('person_id', $id)->delete();
                DB::table('canal_person')->where('person_id', $id)->delete();
                DB::table('person_aliases')->where('person_id', $id)->delete();
                DB::table('persons')->where('id', $id)->delete();
            }
        }

        $now = now();
        foreach ($merge as $from => $to) {
            $fromId = DB::table('persons')->where('normalized_name', $normalize($from))->value('id');
            $toId = DB::table('persons')->where('normalized_name', $normalize($to))->whereNull('deleted_at')->value('id');
            if ($fromId === null || $toId === null || $fromId === $toId) {
                continue;
            }

            $links = DB::table('person_post')->where('person_id', $fromId)->get(['post_id', 'source', 'matched_text', 'confidence']);
            foreach ($links->chunk(500) as $chunk) {
                DB::table('person_post')->insertOrIgnore($chunk->map(fn ($link) => [
                    'person_id' => $toId, 'post_id' => $link->post_id, 'source' => $link->source,
                    'matched_text' => $link->matched_text, 'confidence' => $link->confidence,
                    'created_at' => $now, 'updated_at' => $now,
                ])->all());
            }
            DB::table('person_aliases')->insertOrIgnore([
                'person_id' => $toId, 'name' => $from, 'normalized_name' => $normalize($from),
                'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('person_post')->where('person_id', $fromId)->delete();
            DB::table('canal_person')->where('person_id', $fromId)->delete();
            DB::table('person_aliases')->where('person_id', $fromId)->delete();
            DB::table('persons')->where('id', $fromId)->delete();
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['persons_checked_at']);
            $table->dropColumn('persons_checked_at');
        });
    }
};
