<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('organizations')) {
            return;
        }

        Schema::create('organizations', function (Blueprint $table) {
            // Pôvodná tabuľka kanálov. Od premenovania na `canals` (9/2026)
            // ostáva prázdna, len ako štruktúra.
            $table->increments('id');
            $table->unsignedInteger('village_id');
            $table->boolean('person')->default(false);
            $table->string('avatar', 200)->nullable();
            $table->string('title', 191);
            $table->string('slug', 191);
            $table->string('street', 191)->nullable();
            $table->integer('psc')->nullable();
            $table->string('email', 100)->nullable();
            $table->mediumText('description')->nullable();
            $table->string('mod_title', 20)->nullable();
            // App\Enums\Denomination, CanalKind, CanalSection.
            $table->string('denomination', 20)->nullable();
            $table->string('kind', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('phone_numeric', 20)->nullable();
            $table->string('youtube_channel', 40)->nullable();
            $table->string('youtube_playlist', 40)->nullable();
            // Deň v mesiaci, v ktorý sa kanálu importujú videá z YouTube.
            $table->unsignedTinyInteger('import_day')->nullable()->index('organizations_import_day_index');
            $table->timestamp('youtube_disabled_at')->nullable();
            $table->string('youtube_disabled_reason', 191)->nullable();
            $table->string('url_www', 191)->nullable();
            $table->boolean('published')->default(true);
            $table->string('post_section', 20)->default('front');
            $table->timestamp('front_listed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['front_listed_at', 'kind'], 'organizations_front_list_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
