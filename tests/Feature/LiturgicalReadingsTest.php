<?php

namespace Tests\Feature;

use App\Enums\PostSection;
use App\Models\Canal;
use App\Models\LiturgicalDay;
use App\Models\Post;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Liturgické čítania: sťahovanie z KBS, modul v bočnom paneli, stránka
 * /citania a homílie z archívu. KBS je nahradená uloženými stránkami
 * z tests/Fixtures/kbs.
 */
class LiturgicalReadingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        Carbon::setTestNow('2026-09-14 10:00:00');
        config(['liturgy.pause_ms' => 0, 'liturgy.full_texts' => false]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** KBS odpovedá uloženou stránkou dňa, ak ju máme, inak 404. */
    private function fakeKbs(): void
    {
        Http::fake(['lc.kbs.sk/*' => function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $file = base_path('tests/Fixtures/kbs/day-'.($query['den'] ?? '').'.html');

            return is_file($file)
                ? Http::response(file_get_contents($file))
                : Http::response('', 404);
        }]);
    }

    public function test_prikaz_stiahne_den_a_druhy_raz_ho_preskoci(): void
    {
        $this->fakeKbs();

        $this->artisan('liturgia:stiahnut', ['--od' => '2026-09-14', '--dni' => 1])->assertSuccessful();
        $this->artisan('liturgia:stiahnut', ['--od' => '2026-09-14', '--dni' => 1])->assertSuccessful();

        Http::assertSentCount(1);

        $this->assertDatabaseHas('liturgical_days', [
            'date' => '2026-09-14',
            'title' => 'Povýšenie Svätého kríža',
            'rank' => 'feast',
            'color' => 'red',
            'season' => 'ordinary',
            'week' => 24,
            'sunday_cycle' => 'A',
            'weekday_cycle' => 2,
        ]);

        $day = LiturgicalDay::forDate('2026-09-14')->firstOrFail();
        $this->assertSame('Jn 3, 13-17', $day->gospel()['citation']);
        $this->assertNull($day->gospel()['text']);
    }

    public function test_uvodna_stranka_ukaze_modul_s_citaniami(): void
    {
        $this->fakeKbs();
        $this->artisan('liturgia:stiahnut', ['--od' => '2026-09-14', '--dni' => 1]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Liturgia dňa')
            ->assertSee('Povýšenie Svätého kríža')
            ->assertSee('Nm 21, 4c-9')
            ->assertSee('Jn 3, 13-17')
            ->assertSee('Rok A')
            ->assertSee('1. adventnej nedele')
            // Bez textov vedie citácia na stránku čítaní.
            ->assertSee('#citanie-nm-21-4c-9', false);
    }

    public function test_klik_na_citaciu_v_module_rozbali_text(): void
    {
        config(['liturgy.full_texts' => true]);
        $this->fakeKbs();
        $this->artisan('liturgia:stiahnut', ['--od' => '2026-09-14', '--dni' => 1]);

        $this->get('/')
            ->assertOk()
            ->assertSee('<summary', false)
            ->assertSee('Ľud začal chabnúť na ceste.')
            ->assertSee('Ježiš Kristus, hoci má božskú prirodzenosť')
            ->assertSee('Ježiš povedal Nikodémovi');

        $this->get('/citania')->assertSee('id="citanie-flp-2-6-11"', false);
    }

    public function test_pri_vypadku_kbs_modul_ukaze_vypocitany_den(): void
    {
        config(['liturgy.lazy_fetch' => true]);
        Http::fake(['*' => Http::response('', 500)]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Pondelok 24. týždňa v Cezročnom období')
            ->assertSee('cyklus II')
            ->assertSee('Citácie čítaní sa teraz nepodarilo načítať.');

        $this->assertDatabaseCount('liturgical_days', 0);
    }

    public function test_stranka_si_chybajuci_den_stiahne_sama(): void
    {
        config(['liturgy.lazy_fetch' => true]);
        $this->fakeKbs();

        $this->get('/citania/2026-09-20')
            ->assertOk()
            ->assertSee('25. nedeľa v Cezročnom období')
            ->assertSee('Mt 20, 1-16')
            ->assertSee('Preto na mňa zazeráš, že som dobrý?')
            ->assertDontSee('Nebeské kráľovstvo sa podobá hospodárovi');

        $this->assertDatabaseHas('liturgical_days', ['date' => '2026-09-20', 'rank' => 'sunday']);
    }

    public function test_plne_znenie_len_ked_je_zapnute(): void
    {
        config(['liturgy.lazy_fetch' => true, 'liturgy.full_texts' => true]);
        $this->fakeKbs();

        $this->get('/citania/2026-09-20')
            ->assertOk()
            ->assertSee('Nebeské kráľovstvo sa podobá hospodárovi');
    }

    public function test_neplatny_alebo_vzdialeny_datum_je_404(): void
    {
        $this->get('/citania/2026-02-30')->assertNotFound();
        $this->get('/citania/2030-01-01')->assertNotFound();
        $this->get('/citania/zajtra')->assertNotFound();
        $this->get('/citania')->assertOk();
    }

    public function test_homilie_z_archivu_k_tomu_istemu_evanjeliu(): void
    {
        $canal = Canal::factory()->create(['published' => 1]);

        // 25. nedeľa v Cezročnom období roku A bola naposledy 24. 9. 2023.
        Post::factory()->section(PostSection::Live)->create([
            'canal_id' => $canal->id,
            'title' => 'Svätá omša z Farnosti Brezno',
            'published_at' => '2023-09-24 11:00:00',
        ]);
        Post::factory()->create([
            'canal_id' => $canal->id,
            'title' => 'Koncert chvál v Trnave',
            'published_at' => '2023-09-24 18:00:00',
        ]);
        Post::factory()->create([
            'canal_id' => $canal->id,
            'title' => 'Homília na 25. nedeľu, iný rok',
            'published_at' => '2024-09-22 11:00:00',
        ]);

        // Uloženie príspevku zanechá v session hlášku s jeho názvom.
        $this->flushSession();

        $this->get('/citania/2026-09-20')
            ->assertOk()
            ->assertSee('Toto evanjelium v archíve')
            ->assertSee('Svätá omša z Farnosti Brezno')
            ->assertDontSee('Koncert chvál v Trnave')
            ->assertDontSee('iný rok');
    }
}
