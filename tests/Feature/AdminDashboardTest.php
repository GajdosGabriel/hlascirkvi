<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Prayer;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_vidi_suhrnne_statistiky(): void
    {
        $this->seed(RolesSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);
        $canal = Canal::factory()->create();
        $post = Post::factory()->create(['canal_id' => $canal->id, 'count_view' => 123]);
        Comment::factory()->create(['commentable_id' => $post->id]);
        Prayer::factory()->create(['canal_id' => $canal->id]);

        $this->actingAs($admin)->get('/admin/home')
            ->assertOk()
            ->assertSee('Zhliadnutia celkovo')
            ->assertSee('Otvorené modlitby')
            ->assertDontSee('Rýchla správa')
            ->assertDontSee('<comments-card', false);
    }
    public function test_summary_matches_comment_list_and_counts_deleted_managers_as_missing(): void
    {
        $this->seed(RolesSeeder::class);
        $manager = User::factory()->create();
        $canal = Canal::factory()->create();
        $manager->canals()->attach($canal);
        $post = Post::factory()->create(['canal_id' => $canal->id]);
        $verified = User::factory()->create();
        Comment::factory()->create(['user_id' => $verified->id, 'commentable_id' => $post->id, 'published' => null]);
        Comment::factory()->create(['user_id' => $verified->id, 'commentable_id' => $post->id, 'published' => null, 'source' => 'youtube']);
        Comment::factory()->create(['user_id' => $verified->id, 'commentable_id' => $post->id, 'published' => null, 'youtube_comment_id' => 'external-id']);
        $unverified = User::factory()->create(['email_verified_at' => null]);
        Comment::factory()->create(['user_id' => $unverified->id, 'commentable_id' => $post->id, 'published' => null]);
        $deletedComment = Comment::factory()->create(['user_id' => $verified->id, 'commentable_id' => $post->id, 'published' => null]);
        $deletedComment->delete();
        $manager->delete();

        $stats = app(\App\Services\Dashboard\AdminDashboardStats::class)->get();
        $this->assertSame(Canal::doesntHave('users')->count(), (int) $stats['canals']->orphans);
        $this->assertTrue(Canal::doesntHave('users')->whereKey($canal->id)->exists());
        $this->assertSame(1, (int) $stats['comments']->total);
        $this->assertSame(1, (int) $stats['comments']->unpublished);
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);
        $this->actingAs($admin)->get(route('admin.comment.index'))
            ->assertOk()->assertViewHas('summary', fn ($summary) => (int) $summary->total === 1 && (int) $summary->unpublished === 1);
    }
}