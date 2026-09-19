<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('favorites')) {
            return;
        }

        Schema::create('favorites', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('favorited_id');
            $table->string('favorited_type', 191);
            $table->timestamps();

            $table->unique(['user_id', 'favorited_id', 'favorited_type']);
            $table->index(['favorited_type', 'favorited_id'], 'favorites_favorited_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
