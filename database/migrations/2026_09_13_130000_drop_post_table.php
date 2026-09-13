<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Odstráni omylom vytvorenú singulárnu tabuľku `post`.
 *
 * Aplikácia používa tabuľku `posts`; táto migrácia sa jej nedotýka.
 * `down()` obnoví iba štruktúru tabuľky, nie jej pôvodné dáta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('post');
    }

    public function down(): void
    {
        if (Schema::hasTable('post')) {
            return;
        }

        Schema::create('post', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('organization_id');
            $table->string('title');
            $table->string('slug');
            $table->text('body')->nullable();
            $table->boolean('blocked')->default(false);
            $table->boolean('youtube_blocked')->default(false);
            $table->boolean('video_available')->nullable();
            $table->string('video_id')->nullable();
            $table->string('video_duration')->nullable();
            $table->integer('count_view');
            $table->dateTime('deleted_at');
            $table->dateTime('published')->nullable();
            $table->timestamps();
        });
    }
};
