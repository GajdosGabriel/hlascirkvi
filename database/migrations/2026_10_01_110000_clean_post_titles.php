<?php

use App\Support\TitleNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Titulky príspevkov z importu: bez emoji (a ich zvyškov „?“), bez
     * verzálok a bez výzvy „Všetky podcasty nájdeš na našom YOUTUBE…“ namiesto
     * názvu. Ide priamo cez DB, takže sa nespúšťajú udalosti modelu; slug sa
     * prepočíta len tam, kde sa titulok zmenil.
     *
     * Videá s rovnakým titulkom sú rôzne videá (iné video_id), preto sa
     * nemažú — slabý titulok sa nahradí názvom kanála a dátumom.
     */
    public function up(): void
    {
        $canals = DB::table('canals')->pluck('title', 'id');

        DB::table('posts')->select('id', 'canal_id', 'title', 'slug', 'created_at')
            ->orderBy('id')
            ->chunkById(1000, function ($posts) use ($canals) {
                foreach ($posts as $post) {
                    $title = TitleNormalizer::clean((string) $post->title);

                    if (TitleNormalizer::isPlaceholder($title)) {
                        $title = TitleNormalizer::fallback((string) ($canals[$post->canal_id] ?? ''), $post->created_at);
                    }

                    if ($title === '' || $title === $post->title) {
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
