<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Person;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Osobný kanál ukazuje aj videá iných kanálov, v ktorých
 * je jeho osoba rozpoznaná (náhrada zrušeného hľadania podľa mena).
 */
class PersonCanalPostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function person(Canal $canal): Person
    {
        return Person::create([
            'first_name' => 'Marián', 'last_name' => 'Kuffa', 'name' => 'Marián Kuffa',
            'normalized_name' => Person::normalize('Marián Kuffa'), 'slug' => 'marian-kuffa',
            'canal_id' => $canal->id,
        ]);
    }

    public function test_canal_shows_videos_where_person_appears(): void
    {
        $own = Canal::factory()->create(['youtube_channel' => null, 'youtube_playlist' => null]);
        $zbor = Canal::factory()->create();
        $person = $this->person($own);

        Post::factory()->create(['canal_id' => $own->id, 'title' => 'Vlastné video', 'published_at' => now()]);
        $tagged = Post::factory()->create(['canal_id' => $zbor->id, 'title' => 'Kázeň v zbore', 'published_at' => now()]);
        Post::factory()->create(['canal_id' => $zbor->id, 'title' => 'Iný kazateľ', 'published_at' => now()]);
        $person->posts()->attach($tagged->id, ['source' => 'title', 'confidence' => 90]);

        $this->get('/organizations/' . $own->id)->assertOk()
            ->assertViewHas('posts', fn ($posts) => $posts->pluck('title')->sort()->values()->all() === ['Kázeň v zbore', 'Vlastné video']);
    }

    public function test_canal_with_own_source_also_shows_videos_where_person_appears(): void
    {
        $own = Canal::factory()->create(['youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw']);
        $zbor = Canal::factory()->create();
        $person = $this->person($own);
        $tagged = Post::factory()->create(['canal_id' => $zbor->id, 'title' => 'Kázeň v zbore', 'published_at' => now()]);
        $person->posts()->attach($tagged->id, ['source' => 'title', 'confidence' => 90]);

        $this->get('/organizations/' . $own->id)->assertOk()
            ->assertViewHas('posts', fn ($posts) => $posts->pluck('title')->all() === ['Kázeň v zbore']);
    }
}
