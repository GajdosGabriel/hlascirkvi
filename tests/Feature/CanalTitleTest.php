<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Názov kanála: aspoň 2 znaky, bez emoji, a menovci nedostanú kanál
 * s rovnakým názvom (UserObserver zakladá osobný kanál z mena).
 */
class CanalTitleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    private function manager(Canal $canal): User
    {
        $user = User::factory()->create();
        $canal->users()->attach($user->id);

        return $user->fresh();
    }

    private function save(Canal $canal, string $title)
    {
        return $this->actingAs($this->manager($canal))
            ->put('/dashboard/canals/' . $canal->id, ['title' => $title, 'village_id' => $canal->village_id]);
    }

    public function test_nazov_s_dvoma_znakmi_prejde(): void
    {
        $canal = Canal::factory()->create();

        $this->save($canal, 'Ab')->assertSessionHasNoErrors();
        $this->assertSame('Ab', $canal->fresh()->title);
    }

    public function test_nazov_s_emoji_neprejde(): void
    {
        $canal = Canal::factory()->create();

        $this->save($canal, '🐨 Jani')->assertSessionHasErrors('title');
    }

    public function test_menovci_dostanu_rozne_nazvy_kanalov(): void
    {
        $first = User::factory()->create(['first_name' => 'Mária', 'last_name' => 'Nová']);
        $second = User::factory()->create(['first_name' => 'Mária', 'last_name' => 'Nová']);

        $this->assertSame('Nová Mária', $first->fresh()->canal->title);
        $this->assertSame('Nová Mária (2)', $second->fresh()->canal->title);
    }

    public function test_emoji_z_mena_sa_do_nazvu_kanala_neprenesu(): void
    {
        $this->assertSame('Jani', Canal::uniqueTitle('🐨 Jani'));
        $this->assertSame('Kanál', Canal::uniqueTitle('🙂'));
    }
}
