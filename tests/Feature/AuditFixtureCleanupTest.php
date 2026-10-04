<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditFixtureCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_is_precise_recoverable_and_idempotent(): void
    {
        $canal = Canal::factory()->create();
        $fixture = Post::factory()->create(['canal_id' => $canal->id, 'title' => 'TEST XSS Claude', 'created_at' => '2026-10-01 22:05:33']);
        $legitimate = Post::factory()->create(['canal_id' => $canal->id, 'title' => 'TEST XSS Claude', 'created_at' => '2026-10-02 22:05:33']);
        $migration = require database_path('migrations/2026_10_04_150000_archive_known_audit_fixtures.php');
        $migration->up();
        $migration->up();
        $this->assertSoftDeleted('posts', ['id' => $fixture->id]);
        $this->assertNotSoftDeleted('posts', ['id' => $legitimate->id]);
        $this->assertDatabaseCount('posts', 2);
    }
}
