<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Kanály sa v 9/2026 presunuli z tabuľky `organizations` do `canals`
 * (migrácia 2026_09_16_120000_rename_organizations_to_canals).
 *
 * Test stráži dve veci naraz: že sa dáta naozaj čítajú z `canals`, a že to
 * navonok nie je vidieť — adresy /organizations/{id} a /api/organization/{id}
 * sú zaindexované vo vyhľadávačoch a rozposlané v e-mailoch, takže sa nesmú
 * hnúť ani pri ďalšom upratovaní mien vo vnútri.
 */
class CanalTableRenameTest extends TestCase
{
    use RefreshDatabase;

    public function test_kanaly_stoja_na_tabulke_canals(): void
    {
        $canal = Canal::factory()->create();

        $this->assertSame('canals', $canal->getTable());
        $this->assertDatabaseHas('canals', ['id' => $canal->id]);

        // Pôvodná tabuľka ostáva v databáze, ale prázdna.
        $this->assertTrue(Schema::hasTable('organizations'));
        $this->assertSame(0, DB::table('organizations')->count());
    }

    public function test_cudzie_kluce_sa_volaju_canal_id(): void
    {
        foreach (['posts', 'prayers', 'seminars', 'buffer_publications', 'canal_user'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'canal_id'), $table.' nemá canal_id');
            $this->assertFalse(Schema::hasColumn($table, 'organization_id'), $table.' má ešte organization_id');
        }

        // Aktívny kanál užívateľa. Skratka `org_id` ostala z čias, keď sa
        // kanál volal organizácia.
        $this->assertTrue(Schema::hasColumn('users', 'canal_id'));
        $this->assertFalse(Schema::hasColumn('users', 'org_id'));

        $this->assertFalse(Schema::hasTable('organization_user'));
    }

    public function test_verejna_adresa_kanala_ostava_na_organizations(): void
    {
        $canal = Canal::factory()->create(['published' => now()]);

        $this->assertSame(url('/organizations/'.$canal->id), route('organizations.show', [$canal->id]));

        $this->get('/organizations/'.$canal->id)->assertOk();
        $this->getJson('/api/organization/'.$canal->id)
            ->assertOk()
            ->assertJsonPath('id', $canal->id);
    }

    /**
     * Polymorfné stĺpce niesli triedu `App\Models\Organization` a čítala sa
     * cez morph mapu v AppServiceProvider. Migrácia riadky prepísala, mapa
     * je preč — model teda musí vracať vlastné meno.
     */
    public function test_oblubeny_kanal_sa_uklada_pod_menom_modelu(): void
    {
        // UserObserver novému užívateľovi prideľuje rolu `user`.
        $this->seed(RolesSeeder::class);

        $canal = Canal::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user);
        $canal->toggleFavorite();

        $this->assertSame(Canal::class, $canal->getMorphClass());
        $this->assertDatabaseHas('favorites', [
            'favorited_type' => Canal::class,
            'favorited_id' => $canal->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_prispevok_si_kanal_nacita_vazbou_canal(): void
    {
        $canal = Canal::factory()->create();
        $post = Post::factory()->create(['canal_id' => $canal->id]);

        $this->assertTrue($post->canal()->first()->is($canal));
        $this->assertSame([$post->id], $canal->posts()->pluck('id')->all());
    }
}
