<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Odpovede na komentáre. Vlákno má len jednu úroveň — odpoveď na odpoveď sa
 * zavesí pod ten istý hlavný komentár (App\Http\Requests\SaveCommentsRequest),
 * takže `parent_id` ukazuje vždy na komentár bez rodiča.
 *
 * Bez cudzieho kľúča: komentáre sa mažú mäkko a anonymný komentár je až do
 * schválenia zmazaný od vzniku, odpoveď pod ním teda nesmie padnúť na
 * integritnom obmedzení ani zmiznúť kaskádou.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comments') || Schema::hasColumn('comments', 'parent_id')) {
            return;
        }

        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedInteger('parent_id')->nullable()->after('commentable_type');
            $table->index(['parent_id', 'deleted_at'], 'comments_parent_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('comments', 'parent_id')) {
            return;
        }

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_parent_index');
            $table->dropColumn('parent_id');
        });
    }
};
