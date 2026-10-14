<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabuľka sessions na produkcii vznikla bez indexov, takže GC dopyt
    // `delete from sessions where last_activity <= ?` robí full scan.
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (! Schema::hasIndex('sessions', ['last_activity'])) {
                $table->index('last_activity');
            }
            if (! Schema::hasIndex('sessions', ['user_id'])) {
                $table->index('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['last_activity']);
            $table->dropIndex(['user_id']);
        });
    }
};
