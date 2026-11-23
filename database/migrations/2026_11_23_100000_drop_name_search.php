<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Zrušené hľadanie videí podľa mena (UserSearchByName). Fulltext naprieč
 * YouTube ťahal do osobných kanálov aj cudzie videá — filmy, politické
 * relácie o menovcoch. Videá osôb teraz kanál ukazuje cez rozpoznané osoby.
 *
 * 1. Videá na kanáloch, ktoré plnilo hľadanie podľa mena, a v ktorých osoba
 *    kanála nie je rozpoznaná ani nemá meno s priezviskom v názve, sa zablokujú
 *    (youtube_blocked = 1, rovnako ako tlačidlo „Blokovať“). Nemažú sa;
 *    ich ID sú v logu.
 * 2. Kanál bez YouTube zdroja nemá deň kontroly.
 * 3. Stĺpce okna hľadania podľa mena idú preč.
 */
return new class extends Migration
{
    public function up(): void
    {
        $noSource = fn ($query) => $query
            ->where(fn ($q) => $q->whereNull('youtube_channel')->orWhere('youtube_channel', ''))
            ->where(fn ($q) => $q->whereNull('youtube_playlist')->orWhere('youtube_playlist', ''));

        $canalIds = DB::table('canals')->whereNotNull('import_day')->where($noSource)->pluck('id');
        $persons = DB::table('persons')->whereIn('canal_id', $canalIds)->get(['id', 'canal_id', 'first_name', 'last_name', 'name']);
        $blocked = [];

        foreach ($persons as $person) {
            $surname = $this->fold($person->last_name ?: Str::afterLast((string) $person->name, ' '));
            $first = $this->fold($person->first_name ?: Str::before((string) $person->name, ' '));
            // Meno alebo iniciála s priezviskom („J. Maretta“, „Harčár Martin“).
            // Samotné priezvisko nestačí — „Kuffa“ v politických reláciách je
            // iný Kuffa.
            $named = $surname === '' || $first === '' ? null : sprintf(
                '/\b(?:%1$s|%2$s\.)\s*%3$s\b|\b%3$s,?\s+%1$s\b/u',
                preg_quote($first, '/'), preg_quote($first[0], '/'), preg_quote($surname, '/'),
            );

            DB::table('posts')
                ->where('canal_id', $person->canal_id)
                ->where('youtube_blocked', 0)
                ->whereNull('deleted_at')
                ->whereNotIn('id', fn ($q) => $q->select('post_id')->from('person_post')->where('person_id', $person->id))
                ->orderBy('id')
                ->get(['id', 'title'])
                ->each(function ($post) use ($named, &$blocked) {
                    // Rozpoznávanie mien niečo prehliadlo — také ostáva.
                    if ($named && preg_match($named, $this->fold((string) $post->title))) {
                        return;
                    }
                    $blocked[] = $post->id;
                });
        }

        foreach (array_chunk($blocked, 500) as $chunk) {
            DB::table('posts')->whereIn('id', $chunk)->update(['youtube_blocked' => 1]);
        }

        if ($blocked !== []) {
            Log::info('Zrušené hľadanie podľa mena: zablokované cudzie videá osobných kanálov.', ['post_ids' => $blocked]);
        }

        DB::table('canals')->where($noSource)->update(['import_day' => null, 'video_check_next_at' => null]);

        Schema::table('canals', function (Blueprint $table) {
            $table->dropColumn(['name_search_window_start', 'name_search_window_end', 'name_search_completed_until', 'name_search_page_token']);
        });
    }

    public function down(): void
    {
        Schema::table('canals', function (Blueprint $table) {
            $table->dateTime('name_search_window_start')->nullable();
            $table->dateTime('name_search_window_end')->nullable();
            $table->dateTime('name_search_completed_until')->nullable();
            $table->text('name_search_page_token')->nullable();
        });
    }

    private function fold(string $text): string
    {
        return Str::lower(Str::ascii($text));
    }
};
