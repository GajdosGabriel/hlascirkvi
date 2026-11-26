<?php

use App\Models\Person;
use App\Models\Post;
use App\Services\Persons\PersonDetector;
use App\Services\Persons\PersonMerger;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Oprava osôb po prvom týždni automatického rozpoznávania.
 *
 * 1. „Maroš Kuffa" je Marián Kuffa (domácka podoba). Kým boli dve osoby,
 *    priezvisko „Kuffa" bolo nejednoznačné a „O.Kuffa – kázeň…" nemal nikto.
 * 2. Kanály v persons.canal_default_exclude (zmiešaný obsah) dostali pri
 *    úvodnom naplnení 19. 11. väzbu „canal" na každé video — výnimka vznikla
 *    až potom. Tieto väzby idú preč a videá znova prejdú pravidlami (bez AI).
 *    Video kanála bez YouTube zdroja (plnilo ho hľadanie podľa mena), ktoré
 *    potom osobu nemá, sa zablokuje rovnako ako v 2026_11_23_100000 — väzba
 *    „canal" ho vtedy pred zablokovaním chránila (Linux, AI, futbal u Kodeta).
 * 3. „Sebastian Fors" založila AI z herného videa na takom kanáli.
 */
return new class extends Migration
{
    /**
     * Ručne prezreté videá (YouTube ID) kanálov bez zdroja, v ktorých meno
     * nie je: REVIEWED patria osobe kanála (popis odkazuje na jej web),
     * KEEP sa len nezablokujú (prednášky, autor neistý).
     */
    private const REVIEWED = [
        'kodet-vojtech' => ['_YMtNnpOc3g', 'iKTdf5t2f5w', 'oU-LZTue2fE', '7zqYWcf3kKM', 'fsgN1No9uyo', 'WZkCKu5PCCQ'],
    ];

    private const KEEP = [
        'kodet-vojtech' => ['0IJOUtk8o5I', 'fzgvTmOQJY0', 'AgyhEHj-Qu4'],
    ];

    public function up(): void
    {
        $marian = Person::where('normalized_name', 'marian kuffa')->first();
        $maros = Person::where('normalized_name', 'maros kuffa')->first();
        if ($marian && $maros) {
            app(PersonMerger::class)->merge($maros, $marian);
        }

        $fors = DB::table('persons')->where('normalized_name', 'sebastian fors')->value('id');
        if ($fors !== null) {
            DB::table('person_post')->where('person_id', $fors)->delete();
            DB::table('person_aliases')->where('person_id', $fors)->delete();
            DB::table('canal_person')->where('person_id', $fors)->delete();
            DB::table('persons')->where('id', $fors)->delete();
        }

        $detector = app(PersonDetector::class);
        $blocked = [];

        foreach (config('persons.canal_default_exclude', []) as $slug) {
            $canal = DB::table('canals')->where('slug', $slug)->first(['id', 'youtube_channel', 'youtube_playlist']);
            if (! $canal) {
                continue;
            }
            $personIds = DB::table('persons')->where('canal_id', $canal->id)->pluck('id');
            $postIds = DB::table('person_post')
                ->where('source', 'canal')
                ->whereIn('person_id', $personIds)
                ->whereIn('post_id', fn ($q) => $q->select('id')->from('posts')->where('canal_id', $canal->id))
                ->pluck('post_id');
            if ($postIds->isEmpty()) {
                continue;
            }

            DB::table('person_post')->where('source', 'canal')->whereIn('person_id', $personIds)->whereIn('post_id', $postIds)->delete();

            $noSource = blank($canal->youtube_channel) && blank($canal->youtube_playlist);
            $reviewed = self::REVIEWED[$slug] ?? [];
            $keep = self::KEEP[$slug] ?? [];
            $now = now();
            Post::withoutGlobalScopes()->without(['favorites', 'images', 'canal'])
                ->whereIn('id', $postIds)
                ->get(['id', 'canal_id', 'video_id', 'title', 'body', 'youtube_blocked', 'deleted_at'])
                ->each(function (Post $post) use ($detector, $noSource, $reviewed, $keep, $personIds, $now, &$blocked) {
                    if (in_array($post->video_id, $reviewed, true) && $personIds->count() === 1) {
                        DB::table('person_post')->insertOrIgnore([
                            'person_id' => $personIds->first(), 'post_id' => $post->id, 'source' => 'review',
                            'confidence' => 85, 'created_at' => $now, 'updated_at' => $now,
                        ]);

                        return;
                    }
                    if ($detector->detect($post, false) === [] && $noSource && ! in_array($post->video_id, $keep, true)
                        && ! $post->youtube_blocked && $post->deleted_at === null) {
                        $blocked[] = $post->id;
                    }
                });
        }

        foreach (array_chunk($blocked, 500) as $chunk) {
            DB::table('posts')->whereIn('id', $chunk)->update(['youtube_blocked' => 1]);
        }
        if ($blocked !== []) {
            Log::info('Osoby: zablokované cudzie videá kanálov hľadania podľa mena bez rozpoznanej osoby.', ['post_ids' => $blocked]);
        }

        Artisan::call('persons:detect', ['--recheck' => true]);
    }

    public function down(): void {}
};
