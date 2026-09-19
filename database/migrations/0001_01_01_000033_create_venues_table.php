<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('venues')) {
            return;
        }

        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('village_id')->nullable();
            $table->string('name', 250);
            $table->string('street', 250);
            $table->string('postcode', 250);
            $table->string('slug', 250);
            $table->text('body')->nullable();
            $table->string('website', 50)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
