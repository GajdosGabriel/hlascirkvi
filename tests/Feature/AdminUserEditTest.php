<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserEditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
    }

    private function data(User $user): array
    {
        return [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'status' => 'active',
        ];
    }

    public function test_edit_shows_account_context_and_escapes_profile_text(): void
    {
        $user = User::factory()->create([
            'description' => '</textarea><script>profileSecret()</script>',
            'last_login_via' => 'google', 'last_login_ip' => '192.0.2.42',
        ]);
        $this->actingAs($this->admin)->get(route('admin.user.edit', $user))
            ->assertOk()->assertSee('Profil používateľa')->assertSee('Účet a overenie')
            ->assertSee('Google')->assertSee('192.0.2.42')
            ->assertSee('&lt;/textarea&gt;&lt;script&gt;profileSecret()&lt;/script&gt;', false)
            ->assertDontSee('<script>profileSecret()</script>', false);
        $this->actingAs($this->admin)->get(route('admin.user.edit', $this->admin))
            ->assertOk()->assertSee('Upravujete vlastný administrátorský účet.');
    }

    public function test_profile_is_saved_without_changing_protected_fields(): void
    {
        $user = User::factory()->create(['description' => 'Pôvodný popis']);
        $password = $user->password;
        $verified = $user->email_verified_at;
        $this->actingAs($this->admin)->put(route('admin.user.update', $user), array_merge($this->data($user), [
            'last_name' => '', 'description' => 'Nový popis profilu',
            'password' => 'unwanted-password', 'email_verified_at' => null, 'disabled' => true,
        ]))->assertRedirect(route('admin.user.index'))->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertSame('Nový popis profilu', $user->description);
        $this->assertSame('', $user->last_name);
        $this->assertSame($password, $user->password);
        $this->assertEquals($verified, $user->email_verified_at);
        $this->assertFalse($user->disabled);
        $this->assertNull($user->status_changed_at);

        $this->actingAs($this->admin)->put(route('admin.user.update', $user), $this->data($user) + ['description' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($user->refresh()->description);
    }

    public function test_invalid_profile_is_rejected_and_input_is_preserved(): void
    {
        $user = User::factory()->create(['description' => 'Pôvodný popis']);
        $duplicate = User::factory()->create();
        $edit = route('admin.user.edit', $user);
        $this->actingAs($this->admin)->from($edit)->put(route('admin.user.update', $user), array_merge($this->data($user), [
            'first_name' => str_repeat('a', 51), 'last_name' => str_repeat('b', 51),
            'email' => $duplicate->email, 'description' => str_repeat('c', 5001),
        ]))->assertRedirect($edit)->assertSessionHasErrors(['first_name', 'last_name', 'email', 'description']);
        $this->assertSame('Pôvodný popis', $user->refresh()->description);
        $this->withCookie(config('session.cookie'), session()->getId())->get($edit)->assertOk()->assertViewHas('errors', fn ($errors) => $errors->has('email'))->assertSee('Túto e-mailovú adresu už používa iný účet.')
            ->assertSee('id="email_error"', false)->assertSee('aria-invalid="true"', false)
            ->assertSee($duplicate->email);
    }

    public function test_edit_and_update_are_restricted_to_superadmins(): void
    {
        $user = User::factory()->create();
        $this->get(route('admin.user.edit', $user))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('admin.user.edit', $this->admin))->assertRedirect('/');
        $this->actingAs($user)->put(route('admin.user.update', $this->admin), $this->data($this->admin) + ['description' => 'Nepovolené'])
            ->assertRedirect('/');
        $this->assertNull($this->admin->refresh()->description);
    }
}
