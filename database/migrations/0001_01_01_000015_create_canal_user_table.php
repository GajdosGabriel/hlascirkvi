<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('canal_user')) {
            return;
        }

        Schema::create('canal_user', function (Blueprint $table) {
            // Správcovia kanála.
            $table->unsignedInteger('canal_id')->index();
            $table->unsignedInteger('user_id')->index();

            $table->foreign('canal_id')->references('id')->on('canals')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canal_user');
    }
};
