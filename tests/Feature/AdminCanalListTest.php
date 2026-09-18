<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Výpis kanálov v administrácii: dátum registrácie, aktivita, súhrnné
 * dlaždice a filtre, na ktoré dlaždice odkazujú.
 */
class AdminCanalListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole(['admin', 'superadmin']);
    }

    public function test_karta_ukazuje_registraciu_obsah_a_spravcu(): void
    {
        $canal = Canal::factory()->create(['title' => 'Farnosť Test', 'created_at' => '2025-03-14 10:00:00']);
        $canal->users()->attach($this->admin);
        Post::factory()->count(2)->create(['canal_id' => $canal->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.canal.index'))
            ->assertOk()
            ->assertSee('Registrovaný')
            ->assertSee('14. 3. 2025')
            ->assertSee('Aktívny')
            ->assertSee('príspevky')
            ->assertSee(route('admin.user.edit', $this->admin->id), false)
            ->assertSee('Nové kanály po mesiacoch');
    }

    public function test_filtre_dlazdic_zuzia_vyber(): void
    {
        $fresh = Canal::factory()->create(['title' => 'Čerstvý kanál']);
        $fresh->users()->attach($this->admin);
        Post::factory()->create(['canal_id' => $fresh->id]);

        $old = Canal::factory()->create(['title' => 'Starý kanál', 'created_at' => now()->subYears(2)]);

        $this->actingAs($this->admin)->get(route('admin.canal.index', ['fresh' => 1]))
            ->assertSee('Čerstvý kanál')->assertDontSee('Starý kanál');

        $this->actingAs($this->admin)->get(route('admin.canal.index', ['orphans' => 1]))
            ->assertSee('Starý kanál')->assertDontSee('Čerstvý kanál');

        $this->actingAs($this->admin)->get(route('admin.canal.index', ['silent' => 1]))
            ->assertSee('Starý kanál')->assertDontSee('Čerstvý kanál');

        $this->actingAs($this->admin)->get(route('admin.canal.index', ['month' => $old->created_at->format('Y-m')]))
            ->assertSee('Starý kanál')->assertDontSee('Čerstvý kanál');
    }

    public function test_vsetky_radenia_funguju_a_nezmysel_neublizi(): void
    {
        $quiet = Canal::factory()->create(['title' => 'Aaa tichý']);
        $busy = Canal::factory()->create(['title' => 'Zzz rušný']);
        Post::factory()->count(3)->create(['canal_id' => $busy->id]);

        $this->actingAs($this->admin)->get(route('admin.canal.index', ['sort' => 'posts']))
            ->assertOk()->assertSeeInOrder(['Zzz rušný', 'Aaa tichý']);

        $this->actingAs($this->admin)->get(route('admin.canal.index', ['sort' => 'title']))
            ->assertOk()->assertSeeInOrder(['Aaa tichý', 'Zzz rušný']);

        foreach (['newest', 'oldest', 'active', 'nieco', ''] as $sort) {
            $this->actingAs($this->admin)->get(route('admin.canal.index', ['sort' => $sort, 'month' => 'x']))->assertOk();
        }
    }
}
