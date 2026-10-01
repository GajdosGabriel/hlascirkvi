<?php

use App\Support\TitleNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Staré titulky z importu majú na konci aj v strede hashtagy
     * (`… #godzone #fun`). Import ich odstraňuje od 9/2026, staré riadky nie.
     * `#65` (číslo dielu) ostáva. Titulok, z ktorého by ostal len prázdny
     * text, dostane názov kanála a dátum. Slug sa prepočíta len pri zmene.
     */
    public function up(): void
    {
        $canals = DB::table('canals')->pluck('title', 'id');

        DB::table('posts')->select('id', 'canal_id', 'title', 'created_at')
            ->where('title', 'like', '%#%')
            ->orderBy('id')
            ->chunkById(500, function ($posts) use ($canals) {
                foreach ($posts as $post) {
                    $title = TitleNormalizer::clean((string) $post->title);

                    if ($title === '') {
                        $title = TitleNormalizer::fallback((string) ($canals[$post->canal_id] ?? ''), $post->created_at);
                    }

                    if ($title === $post->title) {
                        continue;
                    }

                    DB::table('posts')->where('id', $post->id)->update([
                        'title' => $title,
                        'slug' => Str::slug($title),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Forward-only: pôvodné titulky sa nedajú zrekonštruovať.
    }
};
