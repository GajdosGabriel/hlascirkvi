<?php

namespace Tests\Feature;

use App\Models\Post;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Laravel\Testing\OpenAIFake;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class PostSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
        config(['openai.api_key' => 'test-key']);
        // Automatické dávky sú predvolene vypnuté (administrácia /admin/ai).
        \App\Models\Setting::set(\App\Services\PostSummarizer::SETTING_ENABLED, 1);
    }

    protected function fakeSummary(string $text): OpenAIFake
    {
        return OpenAI::fake([
            CreateResponse::fake([
                'choices' => [['message' => ['content' => $text]]],
            ]),
        ]);
    }

    public function test_prikaz_ulozi_zhrnutie_dlheho_popisu(): void
    {
        $this->fakeSummary("• Prvá myšlienka\n• Druhá myšlienka");
        $post = Post::factory()->create(['body' => str_repeat('slovo ', 200)]);

        $this->artisan('posts:summarize')->assertSuccessful();

        $post->refresh();
        $this->assertSame("• Prvá myšlienka\n• Druhá myšlienka", $post->summary);
        $this->assertNotNull($post->summary_generated_at);

        $this->get(route('post.show', [$post->id, $post->slug]))
            ->assertOk()
            ->assertSee('V skratke')
            ->assertSee('Prvá myšlienka');
    }

    public function test_kratky_popis_sa_nezhrna_a_neopakuje(): void
    {
        $fake = $this->fakeSummary('nemalo by sa použiť');
        $post = Post::factory()->create(['body' => 'Krátky popis videa.']);

        $this->artisan('posts:summarize')->assertSuccessful();

        $post->refresh();
        $this->assertNull($post->summary);
        $this->assertNotNull($post->summary_generated_at);
        $fake->assertNothingSent();
    }

    public function test_zmena_popisu_zhrnutie_zahodi(): void
    {
        $post = Post::factory()->create(['body' => str_repeat('slovo ', 200)]);
        $post->forceFill(['summary' => 'Staré', 'summary_generated_at' => now()])->saveQuietly();

        $post->update(['body' => str_repeat('iné ', 200)]);

        $post->refresh();
        $this->assertNull($post->summary);
        $this->assertNull($post->summary_generated_at);
    }
}
