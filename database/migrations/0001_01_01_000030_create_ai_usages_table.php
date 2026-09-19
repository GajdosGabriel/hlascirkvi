<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_usages')) {
            return;
        }

        Schema::create('ai_usages', function (Blueprint $table) {
            // Spotreba AI volaní a ich cena.
            $table->increments('id');
            $table->string('feature', 40);
            $table->unsignedInteger('post_id')->nullable()->index();
            $table->string('model', 60);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usages');
    }
};
