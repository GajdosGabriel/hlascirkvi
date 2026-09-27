<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CanalIdentityModeMigrationTest extends TestCase
{
    public function test_identity_backfill_defaults_and_rollback(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('canals', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->nullable()->index();
            $table->timestamp('front_listed_at')->nullable();
            $table->timestamp('published')->nullable();
            $table->softDeletes();
            $table->index(['front_listed_at', 'type'], 'canals_front_list_index');
        });
        foreach (['personal', 'organization', null, '', 'unknown', 'personal'] as $id => $type) {
            DB::table('canals')->insert([
                'id' => $id + 1, 'type' => $type,
                'deleted_at' => $id === 5 ? '2026-09-01 12:00:00' : null,
            ]);
        }

        $migration = require database_path('migrations/2026_09_27_110000_unify_canal_identity_mode.php');
        $migration->up();
        $this->assertFalse(Schema::hasColumn('canals', 'type'));
        DB::table('canals')->insert(['id' => 7]);
        $expected = ['personal', 'organization', 'organization', 'organization', 'organization', 'personal', 'organization'];
        $this->assertSame($expected, DB::table('canals')->orderBy('id')->pluck('identity_mode')->all());
        DB::table('canals')->insert(['id' => 8, 'identity_mode' => 'pseudonymous']);

        $migration->down();
        $this->assertFalse(Schema::hasColumn('canals', 'identity_mode'));
        $this->assertSame([...$expected, 'organization'], DB::table('canals')->orderBy('id')->pluck('type')->all());
    }
}
