<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Koniec číselníka `updaters`.
 *
 * Jedna tabuľka v ňom držala päť nesúvisiacich vecí naraz — cirkevné
 * zaradenie kanála, deň jeho hľadania na YouTube, smerovanie jeho nových
 * videí, predný zoznam na úvodnej stránke a stav zverejnenia príspevku —
 * a kód sa na ne odkazoval natvrdo zapísanými číslami (1, 4, 14, 15, 16, 17).
 * Väzba pritom nikdy nebola M:N: žiadny príspevok nemal viac než jeden
 * updater a žiadny kanál dve vierovyznania, dva dni ani dve zaradenia.
 *
 * Všetko to teraz nesú vlastné stĺpce, ktoré naplnili migrácie
 * 2026_09_12_150000, _160000 a _170000. Tie musia prebehnúť pred touto.
 *
 * Zahadzuje sa aj `posts.published`: import ho vypĺňal každému príspevku hneď
 * pri stiahnutí, takže podľa neho vyzeralo zverejnene aj to, čo ešte čakalo
 * vo fronte — a aplikácia podľa toho ukazovala na nástenke iné čísla než
 * v bufferi. Stav nesie `published_at`.
 *
 * Späť sa to vrátiť nedá: down() postaví prázdne tabuľky, obsah nie.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Poradie je dané cudzími kľúčmi: pivoty najprv, číselník až po nich.
        Schema::dropIfExists('post_updater');
        Schema::dropIfExists('organization_updater');
        Schema::dropIfExists('updaters');

        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'published')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('published');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('updaters')) {
            Schema::create('updaters', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 60)->unique();
                $table->string('slug', 60)->unique();
                $table->unsignedInteger('identificator')->nullable();
                $table->string('type', 40)->nullable();
            });
        }

        if (! Schema::hasTable('post_updater')) {
            Schema::create('post_updater', function (Blueprint $table) {
                $table->unsignedInteger('post_id');
                $table->unsignedInteger('updater_id');

                $table->unique(['post_id', 'updater_id']);
                $table->index('post_id');
                $table->index('updater_id');
                $table->foreign('post_id')->references('id')->on('posts');
                $table->foreign('updater_id')->references('id')->on('updaters');
            });
        }

        if (! Schema::hasTable('organization_updater')) {
            Schema::create('organization_updater', function (Blueprint $table) {
                $table->unsignedInteger('organization_id');
                $table->unsignedInteger('updater_id');

                $table->unique(['organization_id', 'updater_id']);
                $table->index('organization_id');
                $table->index('updater_id');
                $table->foreign('organization_id')->references('id')->on('organizations');
                $table->foreign('updater_id')->references('id')->on('updaters');
            });
        }

        if (Schema::hasTable('posts') && ! Schema::hasColumn('posts', 'published')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dateTime('published')->nullable()->after('count_view');
                $table->index('published');
            });
        }
    }
};
