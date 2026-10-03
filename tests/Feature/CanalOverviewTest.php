<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanalOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_details_share_counts_and_channel_information(): void
    {
        $this->seed(RolesSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);
        $canal = Canal::factory()->create(['email' => 'kontakt@example.org', 'youtube_disabled_at' => now(), 'youtube_disabled_reason' => 'Zdroj nie je dostupný']);
        Post::factory()->count(2)->create(['canal_id' => $canal->id]);
        Post::factory()->unpublished()->create(['canal_id' => $canal->id]);
        Post::factory()->create(['canal_id' => $canal->id])->delete();

        foreach (['admin.canal.show', 'profile.canals.show'] as $route) {
            $response = $this->actingAs($admin)->get(route($route, $canal));
            $response->assertOk()->assertSee('Obsah a aktivita')->assertSee('kontakt@example.org')->assertSee('Zdroj nie je dostupný');
            // Vue removes style tags inside #app when mounting the page.
            $html = $response->getContent();
            $head = substr($html, 0, strpos($html, '</head>'));
            $this->assertStringContainsString('.co-overview', $head);
            $this->assertStringNotContainsString('.co-overview', substr($html, strpos($html, '</head>')));
            $response->assertViewHas('canal', fn ($model) => $model->posts_count === 3
                && $model->published_posts_count === 2 && $model->unpublished_posts_count === 1
                && $model->deleted_posts_count === 1 && $model->favorites_count === 0);
        }
    }

    public function test_manager_sees_active_state_without_admin_links_and_outsider_is_denied(): void
    {
        $this->seed(RolesSeeder::class);
        $canal = Canal::factory()->create();
        $manager = User::factory()->create();
        $canal->users()->attach($manager);
        $manager->update(['canal_id' => $canal->id]);
        $this->actingAs($manager)->get(route('profile.canals.show', $canal))
            ->assertOk()->assertSee('Aktívny kanál pre pridávanie obsahu')
            ->assertDontSee('Prepnúť na tento kanál')->assertDontSee(route('admin.user.edit', $manager), false);
        $this->actingAs(User::factory()->create())->get(route('profile.canals.show', $canal))->assertForbidden();
    }

    public function test_deleted_channel_has_no_unusable_edit_or_switch_actions(): void
    {
        $this->seed(RolesSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);
        $canal = Canal::factory()->create();
        $canal->delete();
        $this->actingAs($admin)->get(route('admin.canal.show', $canal))
            ->assertOk()->assertSee('Zrušený kanál')->assertDontSee('Upraviť profil')->assertDontSee('Prepnúť na tento kanál');
    }
}
