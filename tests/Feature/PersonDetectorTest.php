<?php

namespace Tests\Feature;

use App\Enums\CanalIdentityMode;
use App\Models\AiUsage;
use App\Models\Canal;
use App\Models\Person;
use App\Models\PersonAlias;
use App\Models\Post;
use App\Models\Setting;
use App\Services\Persons\CanalPersonSync;
use App\Services\Persons\PersonDetector;
use App\Services\PostSummarizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class PersonDetectorTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $name, array $attributes = []): Person
    {
        [$first, $last] = explode(' ', $name, 2);

        return Person::create($attributes + [
            'first_name' => $first,
            'last_name' => $last,
            'name' => $name,
            'normalized_name' => Person::normalize($name),
            'slug' => \Illuminate\Support\Str::slug($name),
        ]);
    }

    private function detect(Post $post, bool $ai = true): array
    {
        return app(PersonDetector::class)->detect($post, $ai);
    }

    public function test_meno_v_nazve_aj_v_inom_poradi(): void
    {
        $person = $this->person('Daniel Pastirčák');

        $this->assertSame([$person->id => 'title'], $this->detect(Post::factory()->create(['title' => 'Daniel Pastirčák: Štedrosť'])));
        $this->assertSame([$person->id => 'title'], $this->detect(Post::factory()->create(['title' => 'Homília – Pastircak Daniel'])));
        $this->assertDatabaseHas('person_post', ['person_id' => $person->id, 'source' => 'title', 'confidence' => 90]);
    }

    public function test_sklonovane_meno_a_ina_podoba_v_popise(): void
    {
        $halko = $this->person('Jozef Haľko');
        $elko = $this->person('Ivan Eľko');
        PersonAlias::create(['person_id' => $elko->id, 'name' => 'Ivana Eľka']);

        $post = Post::factory()->create(['title' => 'Nedeľná kázeň', 'body' => '<p>Kázeň biskupa Jozefa Haľka v Bratislave.</p>']);
        $this->assertSame([$halko->id => 'description'], $this->detect($post));

        $post = Post::factory()->create(['title' => 'Milí moji...', 'body' => '<p>Nedeľný príhovor generálneho biskupa ECAV Ivana Eľka</p>']);
        $this->assertSame([$elko->id => 'description'], $this->detect($post));
    }

    public function test_riadok_opakovany_pod_kazdym_videom_kanala_sa_ignoruje(): void
    {
        $this->person('Alois Boháček');
        $canal = Canal::factory()->create();
        $footer = 'Kazatel sboru: Alois Boháček';
        Post::factory()->count(2)->create(['canal_id' => $canal->id, 'body' => "<p>Iná téma</p><p>{$footer}</p>"]);

        $post = Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Bohoslužba', 'body' => "<p>Téma: Viera</p><p>{$footer}</p>"]);

        $this->assertSame([], $this->detect($post));
        $this->assertNotNull($post->fresh()->persons_checked_at);
    }

    public function test_osobny_kanal_bez_ineho_mena(): void
    {
        $canal = Canal::factory()->create(['slug' => 'pastircak-daniel', 'identity_mode' => CanalIdentityMode::Personal]);
        $person = $this->person('Daniel Pastirčák', ['canal_id' => $canal->id]);

        $this->assertSame([$person->id => 'canal'], $this->detect(Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Štedrosť'])));
    }

    public function test_vylucenemu_kanalu_sa_osoba_kanala_nepriradi(): void
    {
        $canal = Canal::factory()->create(['slug' => 'kuffa-marian', 'identity_mode' => CanalIdentityMode::Personal]);
        $this->person('Marián Kuffa', ['canal_id' => $canal->id]);

        $this->assertSame([], $this->detect(Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Genghis Khan war'])));
    }

    public function test_prispevok_s_osobou_sa_uz_nemeni(): void
    {
        $person = $this->person('Daniel Pastirčák');
        $other = $this->person('Ján Buc');
        $post = Post::factory()->create(['title' => 'Ján Buc a hosť']);
        $person->posts()->attach($post->id, ['source' => 'review', 'confidence' => 85]);

        $this->assertSame([], $this->detect($post));
        $this->assertDatabaseMissing('person_post', ['person_id' => $other->id]);
    }

    public function test_ai_je_predvolene_vypnuta(): void
    {
        config(['openai.api_key' => 'test-key']);
        $fake = OpenAI::fake([]);

        $this->assertSame([], $this->detect(Post::factory()->create(['title' => 'Kázeň', 'body' => '<p>Káže brat Novák.</p>'])));
        $fake->assertNothingSent();
    }

    public function test_ai_najde_recnika_a_zalozi_neovereneho(): void
    {
        config(['openai.api_key' => 'test-key']);
        Setting::set(PersonDetector::SETTING_ENABLED, 1);
        Setting::set(PostSummarizer::SETTING_LIMIT, 0);
        $existing = $this->person('Ján Buc');
        OpenAI::fake([CreateResponse::fake([
            'choices' => [['message' => ['content' => '{"speakers": ["Ján Buc", "Peter Novák", "Ježiš"]}']]],
        ])]);

        $post = Post::factory()->create(['title' => 'Večer chvál', 'body' => '<p>Slovom poslúžia hostia.</p>']);
        $found = $this->detect($post);

        $novak = Person::where('name', 'Peter Novák')->firstOrFail();
        $this->assertSame([$existing->id => 'ai', $novak->id => 'ai'], $found);
        $this->assertNull($novak->verified_at);
        $this->assertSame(1, AiUsage::where('feature', PersonDetector::FEATURE)->where('post_id', $post->id)->count());
    }

    public function test_prikaz_spracuje_len_nove_a_prepocita_kanaly(): void
    {
        $person = $this->person('Erik Matej');
        $zbor = Canal::factory()->create(['identity_mode' => CanalIdentityMode::Organization]);
        $posts = Post::factory()->count(5)->create(['canal_id' => $zbor->id, 'title' => 'Erik Matej: kázeň']);
        $old = Post::factory()->create(['title' => 'Erik Matej: staršia']);
        DB::table('posts')->where('id', $old->id)->update(['persons_checked_at' => now()]);

        $this->artisan('persons:detect', ['--no-ai' => true])->assertSuccessful();

        $this->assertSame(5, DB::table('person_post')->where('person_id', $person->id)->count());
        $this->assertDatabaseMissing('person_post', ['post_id' => $old->id]);
        $this->assertDatabaseHas('canal_person', ['person_id' => $person->id, 'canal_id' => $zbor->id, 'source' => 'derived', 'posts_count' => 5]);
    }

    public function test_odvodena_vazba_zanikne_pod_hranicou_a_rucna_ostane(): void
    {
        $person = $this->person('Erik Matej');
        $zbor = Canal::factory()->create();
        $iny = Canal::factory()->create();
        $person->canals()->attach($zbor->id, ['source' => 'derived', 'posts_count' => 9]);
        $person->canals()->attach($iny->id, ['source' => 'manual', 'posts_count' => 0]);

        app(CanalPersonSync::class)->rebuild();

        $this->assertDatabaseMissing('canal_person', ['canal_id' => $zbor->id]);
        $this->assertDatabaseHas('canal_person', ['canal_id' => $iny->id, 'source' => 'manual']);
    }

    public function test_sitemap_osob(): void
    {
        $person = $this->person('Daniel Pastirčák');
        $person->posts()->attach(Post::factory()->create(['published_at' => now()])->id, ['source' => 'title', 'confidence' => 90]);
        $this->person('Bez Videí');

        $this->get('/sitemap-osoby.xml')
            ->assertOk()
            ->assertSee(route('person.show', $person), false)
            ->assertDontSee('bez-videi');
        $this->get('/sitemap.xml')->assertSee(route('sitemap.persons'), false);
    }
}
