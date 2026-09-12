<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Nástenka správcu kanála (/dashboard).
 *
 * Čísla na nej skladá App\Services\Dashboard\DashboardStats surovými dopytmi
 * cez query builder, takže preklep v názve stĺpca sa neprejaví nikde inde —
 * až 500-kou na celej stránke. Presne to sa stalo pri prechode príspevkov na
 * `published_at`: premenovanie sa prenieslo aj do dopytu nad `seminars`,
 * kde taký stĺpec nie je. Test preto stránku naozaj vykreslí.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_spravca_kanala_vidi_nastenku(): void
    {
        $canal = Canal::factory()->create();
        $user  = User::factory()->create(['org_id' => $canal->id]);
        $canal->users()->attach($user->id);

        Post::factory()->create(['organization_id' => $canal->id]);
        Post::factory()->unpublished()->create(['organization_id' => $canal->id]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    /** Užívateľ bez kanála má dostať rozcestník, nie chybu. */
    public function test_uzivatel_bez_kanala_dostane_rozcestnik(): void
    {
        $this->actingAs(User::factory()->create(['org_id' => null]))
            ->get('/dashboard')
            ->assertOk();
    }
}
