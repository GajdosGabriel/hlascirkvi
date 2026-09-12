<?php

namespace Tests\Feature;

use App\Enums\CanalSection;
use App\Enums\Denomination;
use App\Models\Canal;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vlastnosti kanála, ktoré do 9/2026 niesla spojovacia tabuľka
 * `organization_updater`: cirkevné zaradenie, deň hľadania na YouTube
 * a smerovanie nových videí.
 *
 * Strážia sa dve veci: že sa formulárom naozaj uložia, a že tie dve
 * z nich, ktoré patria adminovi, si bežný správca kanála doposlaním poľa
 * prepísať nevie.
 */
class CanalPropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    /** Správca kanála bez roly admin. */
    private function manager(Canal $canal): User
    {
        $user = User::factory()->create();
        $canal->users()->attach($user->id);

        return $user->fresh();
    }

    /**
     * Admin, ktorý zároveň kanál spravuje — samotná rola na úpravu nestačí,
     * prístup ku kanálu nesie policy `manage` (App\Policies\CanalPolicy).
     */
    private function admin(?Canal $canal = null): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $canal?->users()->attach($user->id);

        return $user->fresh();
    }

    /** Formulár posiela celý kanál naraz, preto aj test. */
    private function payload(Canal $canal, array $changes = []): array
    {
        return array_merge([
            'title'      => $canal->title,
            'village_id' => $canal->village_id,
        ], $changes);
    }

    // ------------------------------------------------------------ uloženie

    public function test_spravca_ulozi_cirkevne_zaradenie(): void
    {
        $canal = Canal::factory()->create();

        $this->actingAs($this->manager($canal))
            ->put('/dashboard/canals/' . $canal->id, $this->payload($canal, [
                'denomination' => 'catholic',
            ]))
            ->assertRedirect();

        $this->assertSame(Denomination::Catholic, $canal->fresh()->denomination);
    }

    public function test_admin_ulozi_den_importu_aj_smerovanie_videi(): void
    {
        $canal = Canal::factory()->create();

        $this->actingAs($this->admin($canal))
            ->put('/dashboard/canals/' . $canal->id, $this->payload($canal, [
                'import_day'   => 6,
                'post_section' => 'live',
            ]))
            ->assertRedirect();

        $canal = $canal->fresh();

        $this->assertSame(6, $canal->import_day);
        $this->assertSame(CanalSection::Live, $canal->post_section);
    }

    public function test_spravca_si_den_importu_ani_smerovanie_prepisat_nevie(): void
    {
        // Obe polia vykresľuje formulár len adminovi — bežnému správcovi
        // stačilo doposlať ich v tele požiadavky.
        $canal = Canal::factory()->create(['import_day' => null, 'post_section' => 'front']);

        $this->actingAs($this->manager($canal))
            ->put('/dashboard/canals/' . $canal->id, $this->payload($canal, [
                'import_day'   => 3,
                'post_section' => 'live',
            ]))
            ->assertRedirect();

        $canal = $canal->fresh();

        $this->assertNull($canal->import_day);
        $this->assertSame(CanalSection::Front, $canal->post_section);
    }

    public function test_neznama_hodnota_zaradenia_neprejde(): void
    {
        $canal = Canal::factory()->create();

        $this->actingAs($this->manager($canal))
            ->put('/dashboard/canals/' . $canal->id, $this->payload($canal, [
                'denomination' => 'orthodox',
            ]))
            ->assertSessionHasErrors('denomination');
    }

    public function test_den_mimo_tyzdna_neprejde(): void
    {
        $canal = Canal::factory()->create();

        $this->actingAs($this->admin($canal))
            ->put('/dashboard/canals/' . $canal->id, $this->payload($canal, [
                'import_day' => 7,
            ]))
            ->assertSessionHasErrors('import_day');
    }

    // ------------------------------------------------------------- založenie

    public function test_novy_kanal_ide_cez_buffer(): void
    {
        /*
         * Observer novému kanálu nastavoval updater 1 („živé vysielanie"),
         * čiže jeho videá sa mali zverejňovať hneď pri importe. Predvolené
         * smerovanie je buffer.
         */
        $user = User::factory()->create();

        $this->actingAs($user)->post('/dashboard/canals', [
            'title'        => 'Celkom nový kanál',
            'village_id'   => Village::factory()->create()->id,
            'denomination' => 'evangelical',
        ])->assertRedirect();

        $canal = Canal::where('title', 'Celkom nový kanál')->sole();

        $this->assertSame(CanalSection::Front, $canal->post_section);
        $this->assertSame(Denomination::Evangelical, $canal->denomination);
        $this->assertNull($canal->import_day);
    }

    // ---------------------------------------------------------------- výpisy

    public function test_formular_kanala_ponuka_nove_polia(): void
    {
        $canal = Canal::factory()->create(['denomination' => 'catholic', 'import_day' => 2]);

        $this->actingAs($this->admin($canal))
            ->get('/dashboard/canals/' . $canal->id . '/edit')
            ->assertOk()
            ->assertSee('name="denomination"', false)
            ->assertSee('name="post_section"', false)
            ->assertSee('name="import_day"', false)
            // Zaškrtávadlá updaterov vo formulári už nie sú.
            ->assertDontSee('name="updaters[]"', false);
    }

    public function test_ciselnik_zaradeni_je_dostupny_pre_formular_vo_vue(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/api/denominations')
            ->assertOk()
            ->assertJsonFragment(['value' => 'catholic', 'label' => 'Katolícka'])
            ->assertJsonFragment(['value' => 'evangelical', 'label' => 'Protestantská']);
    }
}
