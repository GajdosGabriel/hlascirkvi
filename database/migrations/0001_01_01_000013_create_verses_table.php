<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Denné verše zo starého importu. Tabuľka má znakovú sadu utf8 a väčšina
 * stĺpcov ešte vlastnú kolaciu; `id` nie je primárny, len unikátny kľúč.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('verses')) {
            return;
        }

        Schema::create('verses', function (Blueprint $table) {
            $table->charset('utf8');
            $table->collation('utf8_slovak_ci');

            $legacy = fn ($column) => $column->nullable()->charset('utf8')->collation('utf8_general_ci');

            $table->unsignedInteger('id');
            $legacy($table->string('title', 93));
            $table->string('slug');
            $legacy($table->string('autor', 29));
            $legacy($table->string('heslomesiaca_text', 149));
            $legacy($table->string('heslomesiaca_ref', 21));
            $legacy($table->string('nazov_sviatku', 68));
            $legacy($table->string('sviatocnyvers_text', 156));
            $legacy($table->string('sviatocnyvers_ref', 18));
            $table->integer('szvers_intro')->nullable();
            $legacy($table->string('szvers_text', 183));
            $legacy($table->string('szvers_ref', 23));
            $table->integer('nzvers_intro')->nullable();
            $legacy($table->string('biblicky_vers', 310));
            $legacy($table->string('biblicky_vers_ref', 25));
            $legacy($table->string('text_na_zamyslenie', 31));
            $legacy($table->string('zamyslenie', 2284));
            $legacy($table->string('modlitba', 645));
            $legacy($table->string('c_piesne', 8));
            $legacy($table->string('piesen', 192));
            $legacy($table->string('bibleref', 69));

            $table->unique('id', 'id');
        });

        // Blueprint vie auto_increment len s primárnym kľúčom.
        DB::statement('ALTER TABLE `verses` MODIFY `id` int unsigned NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        Schema::dropIfExists('verses');
    }
};
