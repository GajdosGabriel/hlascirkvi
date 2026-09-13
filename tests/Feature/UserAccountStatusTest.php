<?php

namespace Tests\Feature;

use App\Enums\ModelStatus;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserAccountStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user;
    }

    public function test_superadmin_zmeni_stav_a_zapise_audit(): void
    {
        $admin = $this->superadmin();
        $user = User::factory()->create();
        $user->createToken('mobil');

        $this->actingAs($admin)->put(route('admin.user.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'status' => ModelStatus::Blocked->value,
            'status_reason' => 'Opakované porušenie pravidiel.',
        ])->assertRedirect(route('admin.user.index'));

        $user->refresh();
        $this->assertSame(ModelStatus::Blocked, $user->status);
        $this->assertSame('Opakované porušenie pravidiel.', $user->status_reason);
        $this->assertSame($admin->id, $user->status_changed_by);
        $this->assertNotNull($user->status_changed_at);
        $this->assertTrue($user->disabled);
        $this->assertCount(0, $user->tokens()->get());
    }

    public function test_neaktivny_stav_vyzaduje_dovod(): void
    {
        $admin = $this->superadmin();
        $user = User::factory()->create();

        $this->actingAs($admin)->from(route('admin.user.edit', $user))->put(route('admin.user.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'status' => ModelStatus::Archived->value,
        ])->assertRedirect(route('admin.user.edit', $user))
            ->assertSessionHasErrors('status_reason');
    }

    public function test_superadmin_nemoze_zablokovat_sam_seba(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->put(route('admin.user.update', $admin), [
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'email' => $admin->email,
            'status' => ModelStatus::Blocked->value,
            'status_reason' => 'Test.',
        ])->assertSessionHasErrors('status');

        $this->assertSame(ModelStatus::Active, $admin->refresh()->status);
    }

    public function test_neaktivny_ucet_nema_pristup_ani_cez_api(): void
    {
        $user = User::factory()->create(['status' => ModelStatus::Blocked]);
        Sanctum::actingAs($user);

        $this->getJson('/api/user')
            ->assertForbidden()
            ->assertJson(['message' => $user->accountAccessMessage()]);
    }
}
