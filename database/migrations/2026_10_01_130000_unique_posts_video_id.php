<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tabuľky s `post_id`, kde sa pri zlúčení väzby presúvajú na ponechaný príspevok. */
    private const POST_ID_TABLES = ['saved_posts', 'person_post', 'post_seminar', 'pending_comments', 'ai_usages'];

    /**
     * Jedno video = jeden príspevok. Import to stráži v kóde
     * (VideoImporter::existingIds), ale dva súbežné behy mohli uložiť to isté
     * `video_id` dvakrát. Duplicity sa zlúčia do jedného príspevku a `video_id`
     * dostane unikátny index (NULL môže byť viackrát — články nemajú video).
     *
     * Ponechá sa príspevok, ktorý nie je zmazaný ani zablokovaný, potom ten
     * s najviac zobrazeniami a najstarší. Komentáre, uložené príspevky a ďalšie
     * väzby sa presunú naň; zobrazenia sa sčítajú do `count_view`.
     */
    public function up(): void
    {
        $videoIds = DB::table('posts')
            ->whereNotNull('video_id')
            ->groupBy('video_id')
            ->havingRaw('count(*) > 1')
            ->pluck('video_id');

        foreach ($videoIds as $videoId) {
            DB::transaction(fn () => $this->merge((string) $videoId));
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->unique('video_id', 'posts_video_id_unique');
            $table->dropIndex('posts_video_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('video_id', 'posts_video_id_index');
            $table->dropUnique('posts_video_id_unique');
        });
    }

    private function merge(string $videoId): void
    {
        $rows = DB::table('posts')
            ->where('video_id', $videoId)
            ->orderByRaw('deleted_at is not null')
            ->orderBy('blocked')
            ->orderBy('youtube_blocked')
            ->orderByRaw('published_at is null')
            ->orderByDesc('count_view')
            ->orderBy('id')
            ->get(['id', 'count_view']);

        $keep = $rows->first()->id;
        $dupes = $rows->skip(1)->pluck('id')->all();

        // UPDATE IGNORE: väzba, ktorá by porušila unikátny kľúč (napr. ten istý
        // používateľ uložil oba príspevky), sa nepresunie a zmizne s duplicitou.
        foreach (self::POST_ID_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::update(
                "update ignore `{$table}` set post_id = ? where post_id in (".implode(',', array_fill(0, count($dupes), '?')).')',
                [$keep, ...$dupes]
            );

            DB::table($table)->whereIn('post_id', $dupes)->delete();
        }

        // Polymorfné väzby (komentáre, obrázky, obľúbené) pod modelom Post.
        foreach ([['comments', 'commentable'], ['images', 'fileable'], ['favorites', 'favorited']] as [$table, $morph]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $marks = implode(',', array_fill(0, count($dupes), '?'));

            DB::update(
                "update ignore `{$table}` set {$morph}_id = ? where {$morph}_type = ? and {$morph}_id in ({$marks})",
                [$keep, Post::class, ...$dupes]
            );
        }

        // Zobrazenia: nepresúvajú sa (unikát po dňoch), len sa sčítajú.
        DB::table('posts')->where('id', $keep)->update(['count_view' => $rows->sum('count_view')]);

        foreach (['views' => 'viewable', 'comments' => 'commentable', 'images' => 'fileable', 'favorites' => 'favorited'] as $table => $morph) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where("{$morph}_type", Post::class)->whereIn("{$morph}_id", $dupes)->delete();
            }
        }

        if (Schema::hasTable('buffer_publications')) {
            DB::table('buffer_publications')->whereIn('post_id', $dupes)->delete();
        }

        DB::table('posts')->whereIn('id', $dupes)->delete();
    }
};
