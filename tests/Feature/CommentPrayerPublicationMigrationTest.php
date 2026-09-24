<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommentPrayerPublicationMigrationTest extends TestCase
{
    public function test_dates_are_backfilled_without_changing_visibility_or_fulfillment(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['comments', 'prayers'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                if ($name === 'comments') {
                    $table->boolean('published')->default(true);
                } else {
                    $table->dateTime('fulfilled_at')->nullable();
                }
                $table->timestamps();
                $table->softDeletes();
            });
            foreach ([1, 2, 3] as $id) {
                DB::table($name)->insert([
                    'id' => $id, 'created_at' => $id === 3 ? '2025-03-29 02:30:00' : '2024-02-28 10:30:45',
                    'updated_at' => '2025-01-01 00:00:00',
                    'deleted_at' => $id === 3 ? '2026-09-01 00:00:00' : null,
                ] + ($name === 'comments'
                    ? ['published' => $id !== 2]
                    : ['fulfilled_at' => $id === 2 ? '2025-03-01 12:00:00' : null]));
            }
        }

        $migration = require database_path('migrations/2026_09_24_130000_add_publication_dates_to_comments_and_prayers.php');
        $migration->up();
        $this->assertSame('datetime', Schema::getColumnType('comments', 'published'));
        $this->assertSame('datetime', Schema::getColumnType('prayers', 'published'));
        $this->assertSame(['2024-02-29 10:30:45', null, '2025-03-30 02:30:00'], DB::table('comments')->orderBy('id')->pluck('published')->all());
        $this->assertSame(['2024-02-29 10:30:45', '2024-02-29 10:30:45', '2025-03-30 02:30:00'], DB::table('prayers')->orderBy('id')->pluck('published')->all());
        $this->assertSame([null, '2025-03-01 12:00:00', null], DB::table('prayers')->orderBy('id')->pluck('fulfilled_at')->all());
        $this->assertSame('2025-01-01 00:00:00', DB::table('comments')->where('id', 1)->value('updated_at'));

        $migration->down();
        $this->assertSame([1, 0, 1], DB::table('comments')->orderBy('id')->pluck('published')->all());
        $this->assertFalse(Schema::hasColumn('prayers', 'published'));
        $this->assertSame([null, '2025-03-01 12:00:00', null], DB::table('prayers')->orderBy('id')->pluck('fulfilled_at')->all());
    }
}
