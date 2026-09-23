<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * 1. `users.gender` (App\Enums\Gender): muž / žena, null = nevieme. Stĺpec
 *    existoval, ale hodnoty prišli z importu first_names s Windows koncom
 *    riadku („female\r"), prípadne ako prázdny reťazec. Upratáva sa aj
 *    first_names, odkiaľ ho UserObserver preberá.
 * 2. `canals.type` (App\Enums\CanalType): osobný kanál po overení e-mailu
 *    vs. organizácia založená z dashboardu. Nahrádza boolean `person`;
 *    staré kanály sa zaradia podľa neho a stĺpec sa zruší.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'gender')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('gender', 10)->nullable()->after('vocative');
            });
        }

        foreach (array_filter(['users', 'first_names'], [Schema::class, 'hasTable']) as $table) {
            DB::table($table)->update(['gender' => DB::raw("NULLIF(TRIM(REPLACE(REPLACE(gender, '\r', ''), '\n', '')), '')")]);
            DB::table($table)->whereNotIn('gender', ['male', 'female'])->update(['gender' => null]);
        }

        if (! Schema::hasColumn('canals', 'type')) {
            Schema::table('canals', function (Blueprint $table) {
                $table->string('type', 20)->nullable()->after('village_id')->index();
            });
        }

        if (Schema::hasColumn('canals', 'person')) {
            DB::table('canals')->whereNull('type')->update([
                'type' => DB::raw("CASE WHEN person = 1 THEN 'personal' ELSE 'organization' END"),
            ]);

            Schema::table('canals', function (Blueprint $table) {
                $table->dropColumn('person');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('canals', 'person')) {
            Schema::table('canals', function (Blueprint $table) {
                $table->boolean('person')->default(false)->after('village_id');
            });

            DB::table('canals')->where('type', 'personal')->update(['person' => 1]);
        }

        if (Schema::hasColumn('canals', 'type')) {
            Schema::table('canals', function (Blueprint $table) {
                $table->dropIndex(['type']);
                $table->dropColumn('type');
            });
        }

        // Gender ostáva — stĺpec existoval už pred touto migráciou.
    }
};
