<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Liturgické čítania na jednotlivé dni, stiahnuté z liturgického kalendára
 * KBS (príkaz liturgia:stiahnut). Obdobie, týždeň a cykly sú vypočítané
 * (App\Services\Liturgy\LiturgicalCalendar), názov dňa, farba a perikopy
 * sú z KBS — tie zohľadňujú aj sviatky svätých a slovenský kalendár.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liturgical_days', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('title', 191);
            // App\Enums\LiturgicalRank, LiturgicalColor, LiturgicalSeason.
            $table->string('rank', 32);
            $table->string('color', 16);
            $table->string('season', 16);
            $table->unsignedTinyInteger('week');
            $table->char('sunday_cycle', 1);
            $table->unsignedTinyInteger('weekday_cycle');
            $table->unsignedTinyInteger('psalter_week')->nullable();
            // Prikázaný sviatok — veriaci majú povinnosť zúčastniť sa na omši.
            $table->boolean('obligation')->default(false);
            // Text pri dni, ktorý nie je stupňom slávenia (napr. pôst na Popolcovú stredu).
            $table->string('note', 191)->nullable();
            // Omše dňa a ich čítania — štruktúru opisuje App\Models\LiturgicalDay.
            $table->json('readings');
            $table->string('source_url', 191);
            $table->dateTime('fetched_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liturgical_days');
    }
};
