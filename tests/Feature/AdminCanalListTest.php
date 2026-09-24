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

    public function test_publication_select_filters_channels(): void
    {
        Canal::factory()->create(['title' => 'Visible channel', 'published' => now()]);
        Canal::factory()->create(['title' => 'Hidden channel', 'published' => null]);
        Canal::factory()->create(['title' => 'Deleted channel'])->delete();

        foreach (['published' => 'Visible channel', 'unpublished' => 'Hidden channel', 'deletedAt' => 'Deleted channel'] as $status => $title) {
            $response = $this->actingAs($this->admin)->get(route('admin.canal.index', ['publication' => $status]));
            $response->assertOk()->assertSee($title);
            foreach (array_diff(['Visible channel', 'Hidden channel', 'Deleted channel'], [$title]) as $other) {
                $response->assertDontSee($other);
            }
        }
    }
    public function test_type_filter_combines_with_publication_and_search(): void
    {
        Canal::factory()->create(['title' => 'Test organization', 'type' => 'organization', 'published' => now()]);
        Canal::factory()->create(['title' => 'Test personal', 'type' => 'personal', 'published' => now()]);
        Canal::factory()->create(['title' => 'Test hidden', 'type' => 'personal', 'published' => null]);

        foreach (['organization', 'personal'] as $type) {
            $this->actingAs($this->admin)
                ->get(route('admin.canal.index', ['type' => $type, 'publication' => 'published', 'search' => 'Test']))
                ->assertOk()
                ->assertSee('Test ' . $type)
                ->assertDontSee('Test ' . ($type === 'personal' ? 'organization' : 'personal'))
                ->assertDontSee('Test hidden');
        }

        foreach (['', 'invalid'] as $type) {
            $this->actingAs($this->admin)->get(route('admin.canal.index', ['type' => $type]))
                ->assertOk()->assertSee('Test organization')->assertSee('Test personal');
        }
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
