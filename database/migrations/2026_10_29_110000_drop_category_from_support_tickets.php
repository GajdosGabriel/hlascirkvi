<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Téma vlákna (otázka, problém, kanál…) bola zbytočná, stačí predmet.
 * Vytváracia migrácia už stĺpec nemá; na produkcii však prebehla ešte
 * s ním, preto ho tu treba zmazať.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('support_tickets', 'category')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    public function down(): void
    {
        // Stĺpec sa nevracia — vytváracia migrácia ho už nemá.
    }
};
