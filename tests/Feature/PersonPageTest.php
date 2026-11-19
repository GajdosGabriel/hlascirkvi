<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Person;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** Vytvorenie príspevku zapíše do session oznámenie s jeho názvom. */
    private function fresh(): static
    {
        session()->flush();

        return $this;
    }

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

    public function test_stranka_osoby_ukaze_jej_videa_a_zbory(): void
    {
        $zbor = Canal::factory()->create(['title' => 'Cirkevný zbor Kaplnka']);
        $kazen = Post::factory()->create(['canal_id' => $zbor->id, 'title' => 'Nedeľná kázeň', 'published_at' => now()]);
        $ine = Post::factory()->create(['title' => 'Cudzie video', 'published_at' => now()]);
        $person = $this->person('Daniel Pastirčák');
        $person->posts()->attach($kazen->id, ['source' => 'title', 'confidence' => 90]);
        $person->canals()->attach($zbor->id, ['source' => 'derived', 'posts_count' => 1]);

        $this->fresh()->get('/osoba/daniel-pastircak')
            ->assertOk()
            ->assertSee('Daniel Pastirčák')
            ->assertSee('Nedeľná kázeň')
            ->assertSee('Cirkevný zbor Kaplnka')
            ->assertDontSee('Cudzie video');
    }

    public function test_video_v_bufferi_sa_na_stranke_osoby_neukaze(): void
    {
        $person = $this->person('Daniel Pastirčák');
        $person->posts()->attach(Post::factory()->unpublished()->create(['title' => 'Čaká v bufferi'])->id, ['source' => 'title', 'confidence' => 90]);

        $this->fresh()->get('/osoba/daniel-pastircak')->assertOk()->assertDontSee('Čaká v bufferi');
    }

    public function test_zmazana_osoba_nema_stranku(): void
    {
        $this->person('Daniel Pastirčák')->delete();

        $this->fresh()->get('/osoba/daniel-pastircak')->assertNotFound();
    }

    public function test_vyhladavanie_ukaze_kartu_osoby(): void
    {
        $person = $this->person('Daniel Pastirčák');
        $person->posts()->attach(Post::factory()->create(['title' => 'Nedeľná kázeň', 'published_at' => now()])->id, ['source' => 'title', 'confidence' => 90]);
        $this->person('Iný Človek');

        $this->fresh()->get('/?search=Pastircak')
            ->assertOk()
            ->assertSee(route('person.show', $person), false)
            ->assertDontSee('Iný Človek');
    }

    public function test_detail_prispevku_ukaze_kto_vo_videu_vystupuje(): void
    {
        $post = Post::factory()->create(['title' => 'Nedeľná kázeň', 'published_at' => now()]);
        $person = $this->person('Daniel Pastirčák');
        $person->posts()->attach($post->id, ['source' => 'title', 'confidence' => 90]);

        $this->fresh()->get(route('post.show', [$post->id, $post->slug]))
            ->assertOk()
            ->assertSee('Vo videu:')
            ->assertSee(route('person.show', $person), false);
    }

    public function test_kanal_ukaze_kto_v_nom_posobi(): void
    {
        $zbor = Canal::factory()->create(['title' => 'Cirkevný zbor Kaplnka']);
        $person = $this->person('Daniel Pastirčák');
        $person->canals()->attach($zbor->id, ['source' => 'derived', 'posts_count' => 5]);

        $this->fresh()->get(route('organizations.show', [$zbor->id]))
            ->assertOk()
            ->assertSee('Pôsobia tu:')
            ->assertSee(route('person.show', $person), false);
    }
}
