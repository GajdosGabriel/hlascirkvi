<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('liturgical_days')) {
            return;
        }

        Schema::create('liturgical_days', function (Blueprint $table) {
            // Liturgické čítania na jednotlivé dni (príkaz liturgia:stiahnut).
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
            $table->boolean('obligation')->default(false);
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
