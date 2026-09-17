<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `settings` — nastavenia prepínané z administrácie (App\Models\Setting),
 * zatiaľ AI zhrnutia. `.env` na to nestačí: zmena by potrebovala nasadenie.
 *
 * `ai_usages` — jeden riadok na volanie OpenAI s počtom tokenov a cenou,
 * z ktorých administrácia ukazuje spotrebu a stráži mesačný limit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->string('key', 100)->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_usages')) {
            Schema::create('ai_usages', function (Blueprint $table) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usages');
        Schema::dropIfExists('settings');
    }
};
