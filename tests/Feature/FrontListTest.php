<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\User;
use App\Services\FrontList\FrontList;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Predný zoznam kanálov („Kresťanské osobnosti" na úvodnej stránke).
 *
 * Strážia sa tu presne tie veci, ktoré pôvodný surový dopyt nad
 * `organization_updater` nerobil: kontrola zmazania a skrytia kanála, počet
 * len zverejnených príspevkov a kanál bez príspevku, ktorý z karty vypadával
 * kvôli INNER JOIN-u.
 */
class FrontListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        app(FrontList::class)->forget();
    }

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user->fresh();
    }

    /** Kanál v zozname, na danom mieste. */
    private function listed(array $attributes = [], ?int $position = null): Canal
    {
        return Canal::factory()->create($attributes + [
            'front_listed_at' => now(),
            'front_position'  => $position,
        ]);
    }

    private function publishPosts(Canal $canal, int $count, array $attributes = []): void
    {
        Post::factory()->count($count)->create(['organization_id' => $canal->id] + $attributes);
    }

    // --------------------------------------------------------------- výber

    public function test_zoznam_drzi_poradie_od_spravcu(): void
    {
        $this->listed(['title' => 'Zadny'], 10);
        $this->listed(['title' => 'Predny'], 5);

        $this->assertSame(
            ['Predny', 'Zadny'],
            app(FrontList::class)->all()->pluck('title')->all()
        );
    }

    public function test_kanal_bez_poradia_ide_na_koniec(): void
    {
        $this->listed(['title' => 'Bez poradia'], null);
        $this->listed(['title' => 'S poradim'], 100);

        $this->assertSame(
            ['S poradim', 'Bez poradia'],
            app(FrontList::class)->all()->pluck('title')->all()
        );
    }

    public function test_skryty_kanal_v_zozname_nie_je(): void
    {
        $this->listed(['title' => 'Skryty', 'published' => 0]);

        $this->assertEmpty(app(FrontList::class)->all());
    }

    public function test_zmazany_kanal_v_zozname_nie_je(): void
    {
        $this->listed(['title' => 'Zmazany'])->delete();

        $this->assertEmpty(app(FrontList::class)->all());
    }

    public function test_kanal_bez_prispevkov_zo_zoznamu_nevypadne(): void
    {
        // Pôvodný dopyt spájal kanály s počtami cez INNER JOIN, takže kanál,
        // z ktorého ešte nič nevyšlo, sa na karte neukázal vôbec.
        $this->listed(['title' => 'Novy kanal']);

        $item = app(FrontList::class)->all()->sole();

        $this->assertSame('Novy kanal', $item->title);
        $this->assertSame(0, $item->postsCount);
    }

    public function test_pocita_sa_len_to_co_je_zverejnene(): void
    {
        $canal = $this->listed();

        $this->publishPosts($canal, 2);
        // Príspevok bez `published_at` čaká v bufferi — návštevník ho neuvidí.
        Post::factory()->unpublished()->count(3)->create(['organization_id' => $canal->id]);

        $this->assertSame(2, app(FrontList::class)->all()->sole()->postsCount);
    }

    public function test_karta_ma_strop_a_cely_zoznam_nie(): void
    {
        config(['frontlist.card_limit' => 2]);

        foreach (range(1, 5) as $i) {
            $this->listed(['title' => 'Kanal ' . $i], $i);
        }

        $frontList = app(FrontList::class);

        $this->assertCount(2, $frontList->forCard());
        $this->assertSame(5, $frontList->total());
    }

    public function test_spiaci_kanal_je_ten_z_ktoreho_dlho_nic_neslo(): void
    {
        $cerstvy = $this->listed(['title' => 'Cerstvy'], 1);
        $spiaci  = $this->listed(['title' => 'Spiaci'], 2);

        $this->publishPosts($cerstvy, 1, ['created_at' => now()->subDays(3)]);
        $this->publishPosts($spiaci, 1, ['created_at' => now()->subYears(3)]);

        $zoznam = app(FrontList::class)->all()->keyBy('title');

        $this->assertFalse($zoznam['Cerstvy']->isStale());
        $this->assertTrue($zoznam['Spiaci']->isStale());
    }

    // -------------------------------------------------------------- stránky

    public function test_uvodna_stranka_ukaze_kartu_predneho_zoznamu(): void
    {
        $this->listed(['title' => 'Kuffa Marian']);

        $this->get('/')->assertOk()->assertSee('Kuffa Marian');
    }

    public function test_verejna_stranka_ukaze_cely_zoznam(): void
    {
        config(['frontlist.card_limit' => 1]);

        $this->listed(['title' => 'Prvy kanal'], 1);
        $this->listed(['title' => 'Druhy kanal'], 2);

        $this->get(route('frontlist.index'))
            ->assertOk()
            ->assertSee('Prvy kanal')
            ->assertSee('Druhy kanal');
    }

    // ---------------------------------------------------------------- správa

    public function test_bezny_uzivatel_sa_k_sprave_zoznamu_nedostane(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/front-list')
            ->assertRedirect('/');
    }

    public function test_superadmin_vidi_zoznam_aj_s_hladanim(): void
    {
        $this->listed(['title' => 'V zozname']);
        Canal::factory()->create(['title' => 'Mimo zoznamu']);

        $this->actingAs($this->superadmin())
            ->get('/admin/front-list?hladat=Mimo')
            ->assertOk()
            ->assertSee('V zozname')
            // Hľadanie ponúka len kanály, ktoré v zozname ešte nie sú.
            ->assertSee('Mimo zoznamu');
    }

    public function test_hladanie_neponuka_kanal_ktory_v_zozname_uz_je(): void
    {
        $this->listed(['title' => 'Uz zaradeny']);

        $this->actingAs($this->superadmin())
            ->get('/admin/front-list?hladat=Uz zaradeny')
            ->assertOk()
            ->assertSee('Nič sa nenašlo');
    }

    public function test_superadmin_prida_kanal_do_zoznamu(): void
    {
        $canal = Canal::factory()->create(['title' => 'Nova osobnost']);

        $this->actingAs($this->superadmin())
            ->post('/admin/front-list', ['canal' => $canal->id])
            ->assertRedirect();

        $this->assertNotNull($canal->fresh()->front_listed_at);
    }

    public function test_superadmin_vyradi_kanal_zo_zoznamu(): void
    {
        $canal = $this->listed();

        $this->actingAs($this->superadmin())
            ->delete('/admin/front-list/' . $canal->id)
            ->assertRedirect();

        $this->assertNull($canal->fresh()->front_listed_at);
        // Vyradenie zo zoznamu nie je zmazanie kanála.
        $this->assertNotNull(Canal::find($canal->id));
    }

    public function test_posun_prehodi_kanal_so_susedom(): void
    {
        $this->listed(['title' => 'Prvy'], 10);
        $druhy = $this->listed(['title' => 'Druhy'], 20);

        $this->actingAs($this->superadmin())
            ->put('/admin/front-list/' . $druhy->id . '/move', ['smer' => 'hore'])
            ->assertRedirect();

        $this->assertSame(
            ['Druhy', 'Prvy'],
            app(FrontList::class)->all()->pluck('title')->all()
        );
    }

    public function test_posun_za_okraj_zoznamu_nic_nezmeni(): void
    {
        $prvy = $this->listed(['title' => 'Prvy'], 10);
        $this->listed(['title' => 'Druhy'], 20);

        $this->actingAs($this->superadmin())
            ->put('/admin/front-list/' . $prvy->id . '/move', ['smer' => 'hore'])
            ->assertRedirect();

        $this->assertSame(
            ['Prvy', 'Druhy'],
            app(FrontList::class)->all()->pluck('title')->all()
        );
    }

    public function test_zmena_v_admine_zahodi_cache(): void
    {
        $this->listed(['title' => 'Povodny']);

        // Naplní cache.
        $this->assertCount(1, app(FrontList::class)->all());

        $novy = Canal::factory()->create(['title' => 'Pridany']);

        $this->actingAs($this->superadmin())->post('/admin/front-list', ['canal' => $novy->id]);

        $this->assertCount(2, app(FrontList::class)->all());
    }
}
