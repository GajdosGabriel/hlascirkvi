<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Prayer;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Výpis modlitieb v administrácii (/admin/prayer).
 *
 * Odkazy na úpravu a mazanie potrebujú kanál modlitby. Kým sa bral cez vzťah
 * (`$prayer->organization->id`), stačila jedna modlitba zo zmazaného kanála —
 * mäkko mazaný kanál sa cez vzťah nenačíta — a spadol celý výpis, nielen
 * jeden riadok.
 */
class AdminPrayerListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    /**
     * Kanál dostáva každý nový užívateľ od App\Observers\UserObserver, takže
     * účet bez neho treba pripraviť zápisom do stĺpca.
     */
    private function superadmin(bool $withCanal = true): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        if (! $withCanal) {
            User::withoutEvents(fn () => $user->update(['org_id' => null]));
        }

        return $user->fresh();
    }

    public function test_vypis_unesie_modlitbu_zo_zmazaneho_kanala(): void
    {
        $canal = Canal::factory()->create();
        Prayer::factory()->create(['organization_id' => $canal->id]);
        $canal->delete();

        $this->actingAs($this->superadmin())
            ->get('/admin/prayer')
            ->assertOk();
    }

    /** Superadmin bez vlastného kanála nemá kam zakladať — stránka však ide. */
    public function test_superadmin_bez_kanala_vidi_vypis(): void
    {
        Prayer::factory()->create();

        $this->actingAs($this->superadmin(false))
            ->get('/admin/prayer')
            ->assertOk()
            ->assertDontSee('Nová modlitba');
    }
}
