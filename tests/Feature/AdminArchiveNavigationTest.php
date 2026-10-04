<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Prayer;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminArchiveNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);
        return $admin;
    }

    public function test_prayer_archive_hides_actions_that_cannot_resolve_deleted_records(): void
    {
        $this->actingAs($this->admin());
        $canal = Canal::factory()->create();
        $deleted = Prayer::factory()->create(['canal_id' => $canal->id]);
        $deleted->delete();
        $orphan = Prayer::factory()->create(['canal_id' => $canal->id]);
        $canal->delete();
        session()->forget('flash');

        $this->get(route('admin.prayer.index', ['deletedAt' => 'true']))->assertOk()
            ->assertSee('Zrušená modlitba')
            ->assertDontSee(route('profile.canals.prayers.edit', [$canal->id, $deleted->id]), false);
        $this->get(route('admin.prayer.index'))->assertOk()
            ->assertSee('Kanál už nie je dostupný')
            ->assertDontSee(route('profile.canals.prayers.edit', [$canal->id, $orphan->id]), false);
    }

    public function test_deleted_user_links_to_readable_detail_without_dead_edit_link(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create();
        $user->delete();
        $this->get(route('admin.user.index', ['status' => 'deleted']))->assertOk()
            ->assertSee(route('admin.user.show', $user->id), false)
            ->assertDontSee(route('admin.user.edit', $user->id), false);
        $this->get(route('admin.user.show', $user->id))->assertOk();
    }
}