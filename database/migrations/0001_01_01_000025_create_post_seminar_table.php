<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('post_seminar')) {
            return;
        }

        Schema::create('post_seminar', function (Blueprint $table) {
            $table->unsignedInteger('seminar_id')->index();
            $table->unsignedInteger('post_id')->index();
            $table->timestamps();

            $table->unique(['post_id', 'seminar_id']);

            $table->foreign('post_id')->references('id')->on('posts');
            $table->foreign('seminar_id')->references('id')->on('seminars');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_seminar');
    }
};
