<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Denný import videí narážal na kanály, ktoré na YouTube už neexistujú
 * (zmazaný účet, prehandlovaná adresa). YouTube na ne odpovedá chybou 403,
 * takže sa tá istá chyba písala do logu každý deň a nikto o nej nevedel.
 *
 * `youtube_disabled_at` import na takom kanáli vypne, `youtube_disabled_reason`
 * drží dôvod pre správcu vo formulári kanála. Po zmene ID kanála alebo
 * playlistu sa import zapína znova (App\Http\Controllers\Canal\CanalController).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organizations') || Schema::hasColumn('organizations', 'youtube_disabled_at')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->timestamp('youtube_disabled_at')->nullable()->after('youtube_playlist');
            $table->string('youtube_disabled_reason', 191)->nullable()->after('youtube_disabled_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('organizations')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['youtube_disabled_at', 'youtube_disabled_reason']);
        });
    }
};
