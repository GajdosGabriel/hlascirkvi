<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Osoba ako samostatná entita vedľa kanála: má vlastný kanál (ak ho má),
 * pôsobí v zboroch či spoločenstvách a vystupuje v konkrétnych videách
 * (person_post už existuje).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('normalized_name');
            // Vlastný kanál osoby (osobný kanál), aby osoba a kanál neboli dve rôzne veci.
            $table->unsignedInteger('canal_id')->nullable()->after('slug');
            // null = osoba rozpoznaná automaticky a ešte neskontrolovaná.
            $table->timestamp('verified_at')->nullable()->after('email');
            $table->foreign('canal_id')->references('id')->on('canals')->nullOnDelete();
        });

        // Iné podoby mena: bez diakritiky, skloňované, s preklepom, prezývka.
        Schema::create('person_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->string('name');
            $table->string('normalized_name');
            $table->timestamps();
            $table->unique(['person_id', 'normalized_name']);
            $table->index('normalized_name');
        });

        // Kto v kanáli (zbore, spoločenstve) pôsobí.
        Schema::create('canal_person', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('canal_id');
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->string('role', 50)->nullable();
            // manual | derived (odvodené z videí) | ai
            $table->string('source', 20);
            $table->unsignedInteger('posts_count')->default(0);
            $table->timestamps();
            $table->foreign('canal_id')->references('id')->on('canals')->cascadeOnDelete();
            $table->unique(['canal_id', 'person_id']);
            $table->index('person_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canal_person');
        Schema::dropIfExists('person_aliases');
        Schema::table('persons', function (Blueprint $table) {
            $table->dropForeign(['canal_id']);
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'canal_id', 'verified_at']);
        });
    }
};
