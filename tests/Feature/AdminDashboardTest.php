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
        $post = Post::factory()->create(['organization_id' => $canal->id, 'count_view' => 123]);
        Comment::factory()->create(['commentable_id' => $post->id]);
        Prayer::factory()->create(['organization_id' => $canal->id]);

        $this->actingAs($admin)->get('/admin/home')
            ->assertOk()
            ->assertSee('Zhliadnutia celkovo')
            ->assertSee('Otvorené modlitby')
            ->assertSee('Rýchla správa')
            ->assertDontSee('<comments-card', false);
    }
}
