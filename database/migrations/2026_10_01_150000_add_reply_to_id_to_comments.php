<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vlákno má jednu úroveň (`parent_id` = hlavný komentár), `reply_to_id`
     * drží komentár, na ktorý sa skutočne odpovedalo. Slúži na upozornenie
     * správneho autora a na zobrazenie „Odpoveď na …" bez textovej zmienky.
     */
    public function up(): void
    {
        foreach (['comments', 'pending_comments'] as $table) {
            if (! Schema::hasColumn($table, 'reply_to_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedInteger('reply_to_id')->nullable()->after('parent_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['comments', 'pending_comments'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('reply_to_id');
            });
        }
    }
};
