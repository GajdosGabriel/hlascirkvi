<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tri vlastnosti kanála, ktoré doteraz niesla spojovacia tabuľka
 * `organization_updater`:
 *
 *  - `denomination`  — cirkevné zaradenie (updatery 12, 13),
 *  - `import_day`    — v ktorý deň sa kanál hľadá na YouTube podľa mena
 *                      (updatery 5–11),
 *  - `post_section`  — kam idú nové videá kanála (updatery 1, 4).
 *
 * Ani jedna z nich nikdy nebola väzbou M:N — dáta to potvrdili: žiadny kanál
 * nemal dve vierovyznania, dva dni ani dve zaradenia. Ako tagy sa na ne
 * pritom v kóde odkazovalo natvrdo zapísanými číslami.
 */
return new class extends Migration
{
    /** Slug updatera dňa → číslo dňa tak, ako ho vracia Carbon::dayOfWeek. */
    protected const DAYS = [
        'nedela'   => 0,
        'pondelok' => 1,
        'utorok'   => 2,
        'streda'   => 3,
        'stvrtok'  => 4,
        'piatok'   => 5,
        'sobota'   => 6,
    ];

    public function up(): void
    {
        if (! Schema::hasTable('organizations') || Schema::hasColumn('organizations', 'post_section')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('denomination', 20)->nullable()->after('mod_title');
            $table->unsignedTinyInteger('import_day')->nullable()->after('youtube_playlist');
            $table->string('post_section', 20)->default('front')->after('published');

            // Denný import si vyberá kanály presne podľa týchto dvoch stĺpcov.
            $table->index('import_day', 'organizations_import_day_index');
        });

        $this->backfill();
    }

    protected function backfill(): void
    {
        if (! Schema::hasTable('organization_updater') || ! Schema::hasTable('updaters')) {
            return;
        }

        $updaters = DB::table('updaters')->pluck('id', 'slug');

        $prepis = function (?int $updaterId, array $values) {
            if (! $updaterId) {
                return;
            }

            DB::table('organizations')
                ->whereIn('id', DB::table('organization_updater')
                    ->where('updater_id', $updaterId)
                    ->pluck('organization_id'))
                ->update($values);
        };

        $prepis($updaters['catholic'] ?? null, ['denomination' => 'catholic']);
        $prepis($updaters['evangelical'] ?? null, ['denomination' => 'evangelical']);

        foreach (self::DAYS as $slug => $cislo) {
            $prepis($updaters[$slug] ?? null, ['import_day' => $cislo]);
        }

        // „Živé vysielanie" je jediné zaradenie, ktoré niečo riadilo; „default"
        // je odteraz predvolená hodnota stĺpca a „vzdelávanie" bol len štítok.
        $prepis($updaters['zive-vysielanie'] ?? null, ['post_section' => 'live']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('organizations') || ! Schema::hasColumn('organizations', 'post_section')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('organizations_import_day_index');
            $table->dropColumn(['denomination', 'import_day', 'post_section']);
        });
    }
};
