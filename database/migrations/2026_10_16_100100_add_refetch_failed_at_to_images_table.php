<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Príznak pre images:refetch: starý náhľad, ktorý sa nahradiť nedal
    // (video zmazané, článok s viac obrázkami). Bez neho ho každá hodinová
    // dávka testovala znova a na zvyšné obrázky sa nedostala.
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            if (! Schema::hasColumn('images', 'refetch_failed_at')) {
                $table->timestamp('refetch_failed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('refetch_failed_at');
        });
    }
};
