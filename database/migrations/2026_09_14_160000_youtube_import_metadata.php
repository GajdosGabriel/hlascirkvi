<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Údaje z YouTube, ktoré import doteraz zahadzoval, a stav synchronizácie
 * komentárov.
 *
 * - `posts.youtube_published_at`: kedy video vyšlo na YouTube (created_at je
 *   okamih importu).
 * - `posts.comments_synced_at`: synchronizácia komentárov sa predtým riadila
 *   prázdnym `video_duration`, takže video bez trvania (ohlásený prenos) sa
 *   skenovalo každú hodinu dookola.
 * - index na `posts.video_id`: import sa pýta na existenciu videa pri každej
 *   položke playlistu. Neunikátny — 91 videí je v databáze dvakrát a na
 *   duplikátoch visia komentáre aj zobrazenia.
 * - `comments.youtube_comment_id`: duplicita sa predtým hľadala podľa textu.
 * - `comments.user_avatar` mal 100 znakov a adresy avatarov z YouTube sú
 *   dlhšie — 24 658 z nich je orezaných a nenačíta sa. Tie sa vynulujú
 *   (zobrazí sa predvolený avatar) a pri ďalšej synchronizácii sa doplnia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'youtube_published_at')) {
                $table->timestamp('youtube_published_at')->nullable()->after('video_duration');
            }

            if (! Schema::hasColumn('posts', 'comments_synced_at')) {
                $table->timestamp('comments_synced_at')->nullable()->after('youtube_published_at');
            }
        });

        if (! Schema::hasIndex('posts', 'posts_video_id_index')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->index('video_id', 'posts_video_id_index');
            });
        }

        Schema::table('comments', function (Blueprint $table) {
            if (! Schema::hasColumn('comments', 'youtube_comment_id')) {
                $table->string('youtube_comment_id', 100)->nullable()->after('parent_id');
                $table->unique('youtube_comment_id', 'comments_youtube_comment_id_unique');
            }

            $table->string('user_avatar', 255)->nullable()->change();
            $table->string('user_name', 100)->nullable()->change();
        });

        DB::table('comments')
            ->where('user_id', 100)
            ->whereRaw('CHAR_LENGTH(user_avatar) = 100')
            ->where('user_avatar', 'like', 'https://yt3.%')
            ->update(['user_avatar' => null]);
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'youtube_comment_id')) {
                $table->dropUnique('comments_youtube_comment_id_unique');
                $table->dropColumn('youtube_comment_id');
            }
        });

        if (Schema::hasIndex('posts', 'posts_video_id_index')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropIndex('posts_video_id_index');
            });
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(array_values(array_filter(
                ['youtube_published_at', 'comments_synced_at'],
                fn ($column) => Schema::hasColumn('posts', $column)
            )));
        });
    }
};
