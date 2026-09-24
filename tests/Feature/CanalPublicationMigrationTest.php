<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CanalPublicationMigrationTest extends TestCase
{
    public function test_legacy_dates_hidden_channels_and_types_are_migrated_and_rollback_works(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('canals', function (Blueprint $table) {
            $table->id();
            $table->boolean('published')->default(true);
            $table->string('kind')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('front_listed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['front_listed_at', 'kind'], 'canals_front_list_index');
        });
        foreach ([
            [1, 1, 'person', 'organization', null],
            [2, 0, 'community', null, null],
            [3, 1, 'person', null, '2026-09-01 10:00:00'],
        ] as [$id, $published, $kind, $type, $deleted]) {
            DB::table('canals')->insert([
                'id' => $id, 'published' => $published, 'kind' => $kind, 'type' => $type,
                'created_at' => '2024-02-28 12:30:45', 'updated_at' => '2025-01-01 00:00:00',
                'deleted_at' => $deleted,
            ]);
        }

        $migration = require database_path('migrations/2026_09_24_120000_convert_canal_publication_and_remove_kind.php');
        $migration->up();
        $this->assertFalse(Schema::hasColumn('canals', 'kind'));
        $this->assertSame('datetime', Schema::getColumnType('canals', 'published'));
        $this->assertSame('2024-02-29 12:30:45', DB::table('canals')->where('id', 1)->value('published'));
        $this->assertSame('2024-02-29 12:30:45', DB::table('canals')->where('id', 3)->value('published'));
        $this->assertNull(DB::table('canals')->where('id', 2)->value('published'));
        $this->assertSame(['organization', 'organization', 'personal'], DB::table('canals')->orderBy('id')->pluck('type')->all());

        $migration->down();
        $this->assertTrue(Schema::hasColumn('canals', 'kind'));
        $this->assertSame([1, 0, 1], DB::table('canals')->orderBy('id')->pluck('published')->all());
    }
}
