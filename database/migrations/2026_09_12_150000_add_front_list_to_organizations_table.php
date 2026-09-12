<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Predný zoznam („Kresťanské osobnosti" v bočnom paneli úvodnej stránky) stál
 * na riadku v `organization_updater` s updaterom 14. Zaradenie kanála tak bolo
 * len tag medzi ôsmimi inými a v kóde sa naň odkazovalo natvrdo zapísaným
 * číslom; poradie v zozname sa nedalo nastaviť vôbec, radilo sa abecedne.
 *
 * `front_listed_at` drží, odkedy je kanál v zozname, `front_position` poradie
 * v ňom. Prázdne poradie ide na koniec — správca nemusí prečíslovať celý
 * zoznam, keď doň pridá jeden kanál.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organizations') || Schema::hasColumn('organizations', 'front_listed_at')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->timestamp('front_listed_at')->nullable()->after('published');
            $table->unsignedSmallInteger('front_position')->nullable()->after('front_listed_at');

            // Zoznam sa načítava na každej stránke s bočným panelom a vracia
            // rádovo desiatky riadkov z piatich stoviek kanálov.
            $table->index(['front_listed_at', 'front_position'], 'organizations_front_list_index');
        });

        $this->backfillFromUpdater();
    }

    /**
     * Prevod z updatera 14. Poradie sa odvodí od abecedy, čo je presne to, čo
     * zoznam ukazoval doteraz — po migrácii teda vyzerá rovnako a správca si
     * ho prerovná až keď chce.
     */
    protected function backfillFromUpdater(): void
    {
        if (! Schema::hasTable('organization_updater') || ! Schema::hasTable('updaters')) {
            return;
        }

        $updaterId = DB::table('updaters')->where('slug', 'front-user')->value('id');

        if (! $updaterId) {
            return;
        }

        $ids = DB::table('organizations')
            ->join('organization_updater', 'organizations.id', '=', 'organization_updater.organization_id')
            ->where('organization_updater.updater_id', $updaterId)
            ->orderBy('organizations.title')
            ->pluck('organizations.id');

        $now = now();

        foreach ($ids as $poradie => $id) {
            DB::table('organizations')->where('id', $id)->update([
                'front_listed_at' => $now,
                'front_position'  => ($poradie + 1) * 10,
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('organizations') || ! Schema::hasColumn('organizations', 'front_listed_at')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('organizations_front_list_index');
            $table->dropColumn(['front_listed_at', 'front_position']);
        });
    }
};
