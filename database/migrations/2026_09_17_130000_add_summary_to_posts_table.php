<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Automatické zhrnutie dlhého popisu (App\Services\PostSummarizer).
 * `summary_generated_at` sa vypĺňa aj vtedy, keď zhrnutie nevzniklo
 * (krátky text) — príkaz posts:summarize sa k príspevku potom nevracia.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('posts', 'summary')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('body');
            $table->timestamp('summary_generated_at')->nullable()->after('summary');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('posts', 'summary')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['summary', 'summary_generated_at']);
        });
    }
};
