<?php

namespace Tests\Feature;

use App\Filters\PostFilters;
use App\Models\Canal;
use App\Models\Person;
use App\Models\PersonAlias;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PostPersonSearchTest extends TestCase
{
    use RefreshDatabase;

    private function search(string $term): array
    {
        $filters = new PostFilters(Request::create('/', 'GET', ['search' => $term]));

        return $filters->apply(Post::query())->pluck('title')->sort()->values()->all();
    }

    private function person(string $first, string $last): Person
    {
        return Person::create([
            'first_name' => $first,
            'last_name' => $last,
            'name' => "$first $last",
            'normalized_name' => Person::normalize("$first $last"),
            'slug' => Person::normalize("$first-$last"),
        ]);
    }

    public function test_hlada_podla_osoby_vo_videu_bez_ohladu_na_diakritiku_a_poradie(): void
    {
        $zbor = Canal::factory()->create(['title' => 'Cirkevný zbor Kaplnka']);
        $kazen = Post::factory()->create(['canal_id' => $zbor->id, 'title' => 'Nedeľná kázeň']);
        Post::factory()->create(['title' => 'Iné video']);
        $this->person('Daniel', 'Pastirčák')->posts()->attach($kazen->id, ['source' => 'title', 'confidence' => 90]);

        $this->assertSame(['Nedeľná kázeň'], $this->search('Pastircak'));
        $this->assertSame(['Nedeľná kázeň'], $this->search('Pastirčák Daniel'));
    }

    public function test_hlada_podla_inej_podoby_mena(): void
    {
        $post = Post::factory()->create(['title' => 'Nedeľný príhovor']);
        $person = $this->person('Ivan', 'Eľko');
        $person->posts()->attach($post->id, ['source' => 'description', 'confidence' => 70]);
        PersonAlias::create(['person_id' => $person->id, 'name' => 'Ivana Eľka']);

        $this->assertSame(['Nedeľný príhovor'], $this->search('Ivana Eľka'));
    }

    public function test_hlada_podla_nazvu_kanala_aj_nazvu_videa(): void
    {
        $zbor = Canal::factory()->create(['title' => 'Cirkevný zbor Kaplnka']);
        Post::factory()->create(['canal_id' => $zbor->id, 'title' => 'Nedeľná kázeň']);
        Post::factory()->create(['title' => 'Večer chvál v kaplnke']);
        Post::factory()->create(['title' => 'Iné video']);

        $this->assertSame(['Nedeľná kázeň'], $this->search('Kaplnka'));
        $this->assertSame(['Večer chvál v kaplnke'], $this->search('kaplnke'));
    }

    public function test_zmazana_osoba_sa_nehlada(): void
    {
        $post = Post::factory()->create(['title' => 'Nedeľná kázeň']);
        $person = $this->person('Daniel', 'Pastirčák');
        $person->posts()->attach($post->id, ['source' => 'title', 'confidence' => 90]);
        $person->delete();

        $this->assertSame([], $this->search('Pastirčák'));
    }
}
