<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mapa vygenerovaných variantov: {"jpg":{"1200":"cesta",...},"webp":{...}}.
     *
     * Stĺpec je nullable zámerne – 58 tisíc starších záznamov ho mať nebude
     * a modelu stačí, keď v tom prípade spadne späť na url/thumb.
     */
    /**
     * Migrácia vytvárajúca tabuľku images v repozitári nie je (staršie
     * migrácie boli odstránené), takže na prázdnej databáze – napríklad
     * v testoch s RefreshDatabase – nie je čo meniť.
     */
    public function up(): void
    {
        if (! Schema::hasTable('images') || Schema::hasColumn('images', 'variants')) {
            return;
        }

        Schema::table('images', function (Blueprint $table) {
            $table->json('variants')->nullable()->after('thumb');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('images') || ! Schema::hasColumn('images', 'variants')) {
            return;
        }

        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('variants');
        });
    }
};
