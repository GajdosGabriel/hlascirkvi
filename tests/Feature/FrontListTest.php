<?php

namespace Tests\Feature;

use App\Enums\CanalKind;
use App\Models\Canal;
use App\Models\Post;
use App\Models\User;
use App\Services\FrontList\FrontList;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Predný zoznam kanálov (karty „Kresťanské osobnosti" a „Cirkvi
 * a spoločenstvá" v bočnom paneli).
 *
 * Strážia sa tu veci, ktoré pôvodný surový dopyt nad `organization_updater`
 * nerobil (zmazanie a skrytie kanála, len zverejnené príspevky, kanál bez
 * príspevku), rozdelenie podľa typu a automatické poradie na karte.
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

    /** Kanál v zozname; predvolene osobnosť. */
    private function listed(array $attributes = []): Canal
    {
        return Canal::factory()->create($attributes + [
            'front_listed_at' => now(),
            'kind'            => CanalKind::Person,
        ]);
    }

    private function publishPosts(Canal $canal, int $count, array $attributes = []): void
    {
        Post::factory()->count($count)->create(['organization_id' => $canal->id] + $attributes);
    }

    /** Toľkoto zhliadnutí príspevku kanála pred daným počtom dní. */
    private function views(Canal $canal, int $count, int $daysAgo = 0): void
    {
        $post = Post::factory()->create([
            'organization_id' => $canal->id,
            'created_at'      => now()->subYears(2),
        ]);

        foreach (range(1, $count) as $i) {
            DB::table('views')->insert([
                'viewable_type' => $post->getMorphClass(),
                'viewable_id'   => $post->id,
                'visitor_hash'  => hash('sha256', $canal->id . ':' . $daysAgo . ':' . $i),
                'viewed_on'     => now()->subDays($daysAgo)->toDateString(),
            ]);
        }
    }

    private function card(CanalKind $kind = CanalKind::Person): array
    {
        return app(FrontList::class)->forCard($kind)->pluck('title')->all();
    }

    // ------------------------------------------------------------------ výber

    public function test_osobnosti_a_spolocenstva_su_oddelene(): void
    {
        $this->listed(['title' => 'Kuffa Marian']);
        $this->listed(['title' => 'ECAV', 'kind' => CanalKind::Community]);

        $this->assertSame(['Kuffa Marian'], $this->card(CanalKind::Person));
        $this->assertSame(['ECAV'], $this->card(CanalKind::Community));
    }

    public function test_kanal_bez_typu_na_webe_nie_je(): void
    {
        $this->listed(['title' => 'Nezaradeny', 'kind' => null]);

        $this->assertEmpty(app(FrontList::class)->all(CanalKind::Person));
        $this->assertEmpty(app(FrontList::class)->all(CanalKind::Community));
        // Správca ho však vidí, aby ho mohol zaradiť.
        $this->assertSame(['Nezaradeny'], app(FrontList::class)->forAdmin()->pluck('title')->all());
    }

    public function test_cely_zoznam_je_abecedny(): void
    {
        $this->listed(['title' => 'Zeman']);
        $this->listed(['title' => 'Adamec']);

        $this->assertSame(
            ['Adamec', 'Zeman'],
            app(FrontList::class)->all(CanalKind::Person)->pluck('title')->all()
        );
    }

    public function test_skryty_kanal_v_zozname_nie_je(): void
    {
        $this->listed(['title' => 'Skryty', 'published' => 0]);

        $this->assertEmpty(app(FrontList::class)->all(CanalKind::Person));
    }

    public function test_zmazany_kanal_v_zozname_nie_je(): void
    {
        $this->listed(['title' => 'Zmazany'])->delete();

        $this->assertEmpty(app(FrontList::class)->all(CanalKind::Person));
    }

    public function test_kanal_bez_prispevkov_zo_zoznamu_nevypadne(): void
    {
        // Pôvodný dopyt spájal kanály s počtami cez INNER JOIN, takže kanál,
        // z ktorého ešte nič nevyšlo, sa na karte neukázal vôbec.
        $this->listed(['title' => 'Novy kanal']);

        $item = app(FrontList::class)->all(CanalKind::Person)->sole();

        $this->assertSame('Novy kanal', $item->title);
        $this->assertSame(0, $item->postsCount);
    }

    public function test_pocita_sa_len_to_co_je_zverejnene(): void
    {
        $canal = $this->listed();

        $this->publishPosts($canal, 2);
        // Príspevok bez `published_at` čaká v bufferi — návštevník ho neuvidí.
        Post::factory()->unpublished()->count(3)->create(['organization_id' => $canal->id]);

        $this->assertSame(2, app(FrontList::class)->all(CanalKind::Person)->sole()->postsCount);
    }

    public function test_spiaci_kanal_je_ten_z_ktoreho_dlho_nic_neslo(): void
    {
        $cerstvy = $this->listed(['title' => 'Cerstvy']);
        $spiaci  = $this->listed(['title' => 'Spiaci']);

        $this->publishPosts($cerstvy, 1, ['created_at' => now()->subDays(3)]);
        $this->publishPosts($spiaci, 1, ['created_at' => now()->subYears(3)]);

        $zoznam = app(FrontList::class)->all(CanalKind::Person)->keyBy('title');

        $this->assertFalse($zoznam['Cerstvy']->isStale());
        $this->assertTrue($zoznam['Spiaci']->isStale());
    }

    // ----------------------------------------------------------- poradie karty

    public function test_karta_radi_podla_nedavneho_zaujmu(): void
    {
        config(['frontlist.discovery_slots' => 0]);

        $this->views($this->listed(['title' => 'Menej']), 2);
        $this->views($this->listed(['title' => 'Viac']), 5);

        $this->assertSame(['Viac', 'Menej'], $this->card());
    }

    public function test_stary_zaujem_vazi_menej_ako_cerstvy(): void
    {
        config(['frontlist.discovery_slots' => 0]);

        // 6 zhliadnutí spred troch týždňov je pri polčase 7 dní 0,75 bodu,
        // 1 dnešné je celý bod. Kto bol „naj" vtedy, dnes navrchu nie je.
        $this->views($this->listed(['title' => 'Kedysi']), 6, 21);
        $this->views($this->listed(['title' => 'Dnes']), 1);

        $this->assertSame(['Dnes', 'Kedysi'], $this->card());
    }

    public function test_zaujem_mimo_okna_sa_nerata(): void
    {
        $canal = $this->listed();
        $this->views($canal, 50, 40);

        $this->assertSame(0.0, app(FrontList::class)->forAdmin()->sole()->score);
    }

    public function test_novy_sledovatel_zvysuje_skore(): void
    {
        config(['frontlist.discovery_slots' => 0, 'frontlist.follow_weight' => 10]);

        $this->views($this->listed(['title' => 'Pozerany']), 5);
        $sledovany = $this->listed(['title' => 'Sledovany']);

        DB::table('favorites')->insert([
            'user_id'        => User::factory()->create()->id,
            'favorited_id'   => $sledovany->id,
            'favorited_type' => $sledovany->getMorphClass(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $this->assertSame(['Sledovany', 'Pozerany'], $this->card());
    }

    public function test_objavovacie_miesto_dostane_kanal_s_novym_obsahom(): void
    {
        config(['frontlist.card_limit' => 3, 'frontlist.discovery_slots' => 1]);

        $this->views($this->listed(['title' => 'Prvy']), 5);
        $this->views($this->listed(['title' => 'Druhy']), 4);
        $this->views($this->listed(['title' => 'Treti']), 3);

        $novy = $this->listed(['title' => 'Novy']);
        $this->publishPosts($novy, 1, ['created_at' => now()->subDays(2)]);

        // Tretí v rebríčku ustúpi kanálu, o ktorom sa ešte nevie.
        $this->assertSame(['Prvy', 'Druhy', 'Novy'], $this->card());
    }

    public function test_bez_kandidatov_na_objavenie_karta_doplni_rebricek(): void
    {
        config(['frontlist.card_limit' => 3, 'frontlist.discovery_slots' => 2]);

        $this->views($this->listed(['title' => 'Prvy']), 5);
        $this->views($this->listed(['title' => 'Druhy']), 4);
        $this->views($this->listed(['title' => 'Treti']), 3);

        $this->assertSame(['Prvy', 'Druhy', 'Treti'], $this->card());
    }

    public function test_karta_ma_strop_a_cely_zoznam_nie(): void
    {
        config(['frontlist.card_limit' => 2]);

        foreach (range(1, 5) as $i) {
            $this->listed(['title' => 'Kanal ' . $i]);
        }

        $frontList = app(FrontList::class);

        $this->assertCount(2, $frontList->forCard(CanalKind::Person));
        $this->assertSame(5, $frontList->total(CanalKind::Person));
    }

    // ---------------------------------------------------------------- stránky

    public function test_uvodna_stranka_ukaze_obe_karty(): void
    {
        $this->listed(['title' => 'Kuffa Marian']);
        $this->listed(['title' => 'TV LUX', 'kind' => CanalKind::Community]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Kresťanské osobnosti')
            ->assertSee('Kuffa Marian')
            ->assertSee('Cirkvi a spoločenstvá')
            ->assertSee('TV LUX');
    }

    public function test_verejna_stranka_ukaze_cely_zoznam_po_typoch(): void
    {
        config(['frontlist.card_limit' => 1]);

        $this->listed(['title' => 'Prvy kanal']);
        $this->listed(['title' => 'Druhy kanal']);
        $this->listed(['title' => 'Spolocenstvo X', 'kind' => CanalKind::Community]);

        $this->get(route('frontlist.index'))
            ->assertOk()
            ->assertSee('id="osobnosti"', false)
            ->assertSee('id="spolocenstva"', false)
            ->assertSee('Prvy kanal')
            ->assertSee('Druhy kanal')
            ->assertSee('Spolocenstvo X');
    }

    // ----------------------------------------------------------------- správa

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

    public function test_superadmin_prida_kanal_aj_s_typom(): void
    {
        $canal = Canal::factory()->create(['title' => 'Nova komunita']);

        $this->actingAs($this->superadmin())
            ->post('/admin/front-list', ['canal' => $canal->id, 'kind' => 'community'])
            ->assertRedirect();

        $canal->refresh();
        $this->assertNotNull($canal->front_listed_at);
        $this->assertSame(CanalKind::Community, $canal->kind);
    }

    public function test_pridanie_bez_typu_neprejde(): void
    {
        $canal = Canal::factory()->create();

        $this->actingAs($this->superadmin())
            ->post('/admin/front-list', ['canal' => $canal->id])
            ->assertSessionHasErrors('kind');

        $this->assertNull($canal->fresh()->front_listed_at);
    }

    public function test_superadmin_prepne_typ_kanala(): void
    {
        $canal = $this->listed(['title' => 'ECAV']);

        $this->actingAs($this->superadmin())
            ->put('/admin/front-list/' . $canal->id . '/kind', ['kind' => 'community'])
            ->assertRedirect();

        $this->assertSame(['ECAV'], $this->card(CanalKind::Community));
        $this->assertSame([], $this->card(CanalKind::Person));
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

    public function test_zmena_v_admine_zahodi_cache(): void
    {
        $this->listed(['title' => 'Povodny']);

        // Naplní cache.
        $this->assertCount(1, app(FrontList::class)->all(CanalKind::Person));

        $novy = Canal::factory()->create(['title' => 'Pridany']);

        $this->actingAs($this->superadmin())->post('/admin/front-list', ['canal' => $novy->id, 'kind' => 'person']);

        $this->assertCount(2, app(FrontList::class)->all(CanalKind::Person));
    }
}
