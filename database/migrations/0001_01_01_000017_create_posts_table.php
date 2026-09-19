<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts')) {
            return;
        }

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('canal_id');
            $table->string('title', 200);
            $table->text('body');
            // AI zhrnutie (príkaz PostSummarize).
            $table->text('summary')->nullable();
            $table->timestamp('summary_generated_at')->nullable();
            $table->string('slug', 191);
            $table->boolean('blocked')->default(false);
            $table->boolean('youtube_blocked')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->string('video_id', 191)->nullable()->index('posts_video_id_index');
            // Denormalizovaný počet zobrazení; jednotlivé zobrazenia drží `views`.
            $table->integer('count_view')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->string('section', 20)->default('front');
            $table->boolean('video_available')->nullable();
            $table->string('video_duration', 15)->nullable();
            $table->timestamp('youtube_published_at')->nullable();
            $table->timestamp('comments_synced_at')->nullable();

            // Indexy kopírujú reálne dopyty: najprv stĺpce porovnávané na
            // rovnosť (vrátane IS NULL), až potom stĺpec, podľa ktorého sa radí.
            $table->index(['youtube_blocked', 'video_available', 'deleted_at', 'created_at'], 'posts_feed_created_index');
            $table->index(['youtube_blocked', 'video_available', 'deleted_at', 'count_view'], 'posts_feed_views_index');
            $table->index(['section', 'published_at'], 'posts_section_published_index');
            $table->index(['canal_id', 'youtube_blocked', 'deleted_at', 'created_at'], 'posts_canal_created_index');
            $table->index(['youtube_blocked', 'canal_id'], 'posts_canal_count_index');
            $table->index(['canal_id', 'youtube_blocked', 'deleted_at', 'count_view'], 'posts_canal_views_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
