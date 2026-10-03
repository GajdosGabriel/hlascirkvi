<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NamedCanalPortraitMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'images.disk' => 'public']);
        DB::purge('sqlite');
        Schema::create('canals', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('title');
            $table->string('identity_mode')->default('organization');
            $table->string('avatar')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Storage::fake('public');
    }

    public function test_uploads_portraits_and_preserves_existing_avatars_on_rerun(): void
    {
        foreach ([359 => 'František Trstenský', 360 => 'Mário Tomášik', 361 => 'Michal Zamkovský'] as $id => $title) {
            DB::table('canals')->insert(['id' => $id, 'title' => $title, 'avatar' => $id === 360 ? '' : null]);
        }
        $migration = require database_path('migrations/2026_10_02_100000_fill_named_canal_portraits.php');
        $migration->up();
        foreach ([359 => 'gif', 360 => 'png', 361 => 'jpg'] as $id => $extension) {
            $name = 'portrait-2026-10-02.'.$extension;
            $this->assertSame($name, DB::table('canals')->where('id', $id)->value('avatar'));
            $this->assertSame(file_get_contents(database_path('data/canal-person-portraits/'.$id.'.'.$extension)), Storage::disk('public')->get('organizations/'.$id.'/'.$name));
        }
        DB::table('canals')->where('id', 360)->update(['avatar' => 'custom.png']);
        $migration->up();
        $migration->down();
        $this->assertSame('custom.png', DB::table('canals')->where('id', 360)->value('avatar'));
    }

    public function test_skips_wrong_identity_deleted_channels_and_existing_avatars(): void
    {
        DB::table('canals')->insert([
            ['id' => 359, 'title' => 'František Trstenský', 'identity_mode' => 'organization', 'avatar' => 'existing.jpg', 'deleted_at' => null],
            ['id' => 360, 'title' => 'Mário Tomášik', 'identity_mode' => 'organization', 'avatar' => null, 'deleted_at' => now()],
            ['id' => 361, 'title' => 'Different person', 'identity_mode' => 'organization', 'avatar' => null, 'deleted_at' => null],
        ]);
        $migration = require database_path('migrations/2026_10_02_100000_fill_named_canal_portraits.php');
        $migration->up();
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertSame('existing.jpg', DB::table('canals')->where('id', 359)->value('avatar'));
        DB::table('canals')->where('id', 361)->update(['title' => 'Michal Zamkovský', 'identity_mode' => 'personal']);
        $migration->up();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_upload_does_not_set_avatar(): void
    {
        DB::table('canals')->insert(['id' => 359, 'title' => 'František Trstenský']);
        $disk = \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
        $disk->shouldReceive('put')->once()->andReturn(false);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);
        $migration = require database_path('migrations/2026_10_02_100000_fill_named_canal_portraits.php');
        try {
            $migration->up();
            $this->fail('Expected upload failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Cannot upload canal portrait: 359', $exception->getMessage());
        }
        $this->assertNull(DB::table('canals')->where('id', 359)->value('avatar'));
    }
}
