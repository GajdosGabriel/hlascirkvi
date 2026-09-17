<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * „Uložiť na neskôr". Súkromná záložka prihláseného čitateľa — na rozdiel
 * od odporúčania (tabuľka favorites) ju nikto iný nevidí a nepočíta sa.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saved_posts')) {
            return;
        }

        Schema::create('saved_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('post_id');
            $table->timestamps();

            $table->unique(['user_id', 'post_id']);
            // Stránka „Uložené" radí od posledného uloženia.
            $table->index(['user_id', 'created_at']);
            $table->index('post_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_posts');
    }
};
