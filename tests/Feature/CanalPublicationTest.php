<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanalPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_canals_publish_now_without_a_one_day_offset(): void
    {
        $this->freezeTime();
        $canal = Canal::factory()->create(['created_at' => now()->subWeek()]);
        $this->assertSame(now()->toDateTimeString(), $canal->fresh()->published->toDateTimeString());
        $this->assertNull(Canal::factory()->unpublished()->create()->fresh()->published);
    }

    public function test_superadmin_can_change_and_clear_publication_date(): void
    {
        $this->seed(RolesSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);
        $canal = Canal::factory()->create();
        $data = ['title' => $canal->title, 'village_id' => $canal->village_id];

        $this->actingAs($admin)->put("/dashboard/canals/{$canal->id}", $data + [
            'published' => '2026-09-20T12:30:45',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('2026-09-20 12:30:45', $canal->fresh()->published->toDateTimeString());

        $this->actingAs($admin)->put("/dashboard/canals/{$canal->id}", $data)
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('2026-09-20 12:30:45', $canal->fresh()->published->toDateTimeString());

        $this->actingAs($admin)->put("/dashboard/canals/{$canal->id}", $data + [
            'published' => '',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($canal->fresh()->published);

        $this->actingAs($admin)->put("/dashboard/canals/{$canal->id}", $data + [
            'published' => 'invalid',
        ])->assertSessionHasErrors('published');
    }
}
