<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('images')) {
            return;
        }

        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fileable_id')->index();
            $table->string('fileable_type', 191);
            $table->string('name', 191);
            $table->string('url', 191);
            $table->string('thumb', 191);
            // Zmenšeniny obrázka v rôznych šírkach a formátoch.
            $table->json('variants')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_primary')->nullable();
            $table->string('org_name', 191);
            $table->integer('size')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->string('mime', 10)->nullable();
            $table->enum('type', ['img', 'video', 'card'])->nullable();

            $table->unique(['fileable_type', 'fileable_id', 'url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
