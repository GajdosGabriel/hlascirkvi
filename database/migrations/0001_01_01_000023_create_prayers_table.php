<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('prayers')) {
            return;
        }

        Schema::create('prayers', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('canal_id')->index('prayers_canal_id_index');
            $table->string('user_name')->nullable();
            $table->text('body');
            $table->dateTime('fulfilled_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['deleted_at', 'created_at'], 'prayers_created_index');
            $table->index(['deleted_at', 'fulfilled_at'], 'prayers_fulfilled_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayers');
    }
};
