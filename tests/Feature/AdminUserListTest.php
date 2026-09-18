<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Výpis používateľov v administrácii: súhrnné dlaždice, ich filtre
 * a radenie klikom na hlavičku tabuľky.
 */
class AdminUserListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Hlavný',
            'last_login_at' => now(),
            'last_login_via' => 'password',
        ]);
        $this->admin->assignRole('superadmin');
    }

    public function test_dlazdice_a_rozpad_prihlaseni(): void
    {
        User::factory()->create(['last_login_at' => now()->subDay(), 'last_login_via' => 'google']);
        User::factory()->create(['last_login_at' => null, 'created_at' => now()->subYear()]);

        $this->actingAs($this->admin)
            ->get(route('admin.user.index'))
            ->assertOk()
            ->assertViewHas('summary', fn ($s) => $s->total === 3 && $s->active === 2 && $s->never === 1 && $s->fresh === 2
                && $s->via == ['google' => 1, 'password' => 1])
            ->assertSee('nikdy neprihlásení')
            ->assertSee('Prihlásenie cez');
    }

    public function test_filtre_dlazdic_zuzia_vyber(): void
    {
        User::factory()->create(['first_name' => 'Nováčik', 'last_login_at' => null]);
        User::factory()->create(['first_name' => 'Googler', 'last_login_at' => now(), 'last_login_via' => 'google', 'created_at' => now()->subYear()]);

        $this->actingAs($this->admin)->get(route('admin.user.index', ['never' => 1]))
            ->assertSee('Nováčik')->assertDontSee('Googler');

        $this->actingAs($this->admin)->get(route('admin.user.index', ['via' => 'google']))
            ->assertSee('Googler')->assertDontSee('Nováčik');

        $this->actingAs($this->admin)->get(route('admin.user.index', ['fresh' => 1]))
            ->assertSee('Nováčik')->assertDontSee('Googler');
    }

    public function test_radenie_podla_stlpcov(): void
    {
        User::factory()->create(['first_name' => 'Anna', 'last_name' => 'Zelená', 'email' => 'z@example.com', 'last_login_at' => null]);
        User::factory()->create(['first_name' => 'Boris', 'last_name' => 'Adamec', 'email' => 'a@example.com', 'last_login_at' => now()->subDays(3)]);

        $names = fn (string $sort) => $this->actingAs($this->admin)
            ->get(route('admin.user.index', ['sort' => $sort]))
            ->assertOk()
            ->viewData('users')->pluck('first_name')->all();

        $this->assertSame(['Boris', 'Admin', 'Anna'], $names('name'));
        $this->assertSame(['Anna', 'Admin', 'Boris'], $names('-name'));
        $this->assertSame('Boris', $names('email')[0]);
        // Nikdy neprihlásený je na konci v oboch smeroch.
        $this->assertSame(['Admin', 'Boris', 'Anna'], $names('-login'));
        $this->assertSame(['Boris', 'Admin', 'Anna'], $names('login'));
    }

    public function test_hladanie_v_liste_filtrov_zachova_filter_dlazdice(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.user.index', ['unverified' => 1]))
            ->assertOk()
            ->assertSee('<input type="hidden" name="unverified" value="1">', false);
    }

    public function test_hlavicka_obsahuje_odkazy_na_radenie(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.user.index', ['sort' => 'email']))
            ->assertOk()
            ->assertSee('sort=-email', false)
            ->assertSee('sort=-created', false)
            ->assertSee('aria-sort="ascending"', false);
    }
}
