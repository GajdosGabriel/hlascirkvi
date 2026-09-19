<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('first_names')) {
            return;
        }

        Schema::create('first_names', function (Blueprint $table) {
            // Číselník krstných mien pre oslovenie (UserObserver).
            $table->increments('id');
            $table->string('name', 100)->nullable();
            $table->string('vocative', 100)->nullable();
            $table->integer('count')->default(0);
            $table->string('gender', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('first_names');
    }
};
