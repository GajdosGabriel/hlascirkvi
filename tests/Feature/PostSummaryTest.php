<?php

namespace Tests\Feature;

use App\Models\BufferPublication;
use App\Models\Post;
use App\Models\Setting;
use App\Models\AiUsage;
use App\Services\Buffer;
use App\Services\PostSummarizer;
use App\Services\YoutubeCaptions;
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
        Setting::set(PostSummarizer::SETTING_ENABLED, 1);
    }

    protected function fakeSummary(string $text): OpenAIFake
    {
        return OpenAI::fake([
            CreateResponse::fake([
                'choices' => [['message' => ['content' => $text]]],
            ]),
        ]);
    }

    public function test_buffer_checks_only_selected_video_before_publication(): void
    {
        $this->fakeSummary('Najzaujímavejšia myšlienka');
        $post = Post::factory()->unpublished()->create(['video_id' => 'selected']);
        $waiting = Post::factory()->unpublished()->create(['canal_id' => $post->canal_id, 'video_id' => 'waiting']);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->once()->with('selected')
            ->andReturnUsing(function () use ($post) {
                $this->assertNull($post->fresh()->published_at);
                $this->assertDatabaseMissing('buffer_publications', ['post_id' => $post->id]);
                return str_repeat('titulky ', 200);
            });

        $published = app(Buffer::class)->handler(true);

        $this->assertSame($post->id, $published->id);
        $this->assertSame('Najzaujímavejšia myšlienka', $post->fresh()->summary);
        $this->assertNotNull($post->fresh()->published_at);
        $this->assertNull($waiting->fresh()->summary_generated_at);
        $this->assertNull($waiting->fresh()->published_at);
        $this->artisan('posts:summarize')->assertSuccessful();
    }

    public function test_buffer_without_captions_publishes_without_summary(): void
    {
        $fake = OpenAI::fake([]);
        $post = Post::factory()->unpublished()->create(['video_id' => 'empty']);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->once()->andReturn(null);
        app(Buffer::class)->handler(true);
        $this->assertNotNull($post->fresh()->published_at);
        $this->assertNotNull($post->fresh()->summary_generated_at);
        $this->assertNull($post->fresh()->summary);
        $fake->assertNothingSent();
    }

    public function test_buffer_summary_guards_preserve_publication(): void
    {
        $fake = OpenAI::fake([]);
        $this->mock(YoutubeCaptions::class)->shouldNotReceive('text');
        foreach (['disabled', 'unconfigured', 'budget', 'existing', 'checked', 'no_video'] as $case) {
            Setting::set(PostSummarizer::SETTING_ENABLED, $case !== 'disabled');
            Setting::set(PostSummarizer::SETTING_LIMIT, $case === 'budget' ? 1 : 0);
            config(['openai.api_key' => $case === 'unconfigured' ? '' : 'test-key']);
            if ($case === 'budget') {
                AiUsage::create(['feature' => 'post_summary', 'model' => 'test', 'prompt_tokens' => 1, 'completion_tokens' => 1, 'cost_usd' => 2]);
            }
            $post = Post::factory()->unpublished()->create([
                'video_id' => $case === 'no_video' ? null : $case,
                'summary' => $case === 'existing' ? 'Pôvodné zhrnutie' : null,
                'summary_generated_at' => $case === 'checked' ? now() : null,
            ]);
            $this->assertSame($post->id, app(Buffer::class)->handler(true)->id);
            $this->assertNotNull($post->fresh()->published_at);
            if ($case === 'existing') {
                $this->assertSame('Pôvodné zhrnutie', $post->fresh()->summary);
            }
        }
        $fake->assertNothingSent();
    }

    public function test_caption_failure_does_not_stop_buffer_publication(): void
    {
        $post = Post::factory()->unpublished()->create(['video_id' => 'failed']);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->once()->andThrow(new \RuntimeException('Unavailable'));
        app(Buffer::class)->handler(true);
        $this->assertNotNull($post->fresh()->published_at);
        $this->assertNull($post->fresh()->summary_generated_at);
        $this->assertDatabaseHas('buffer_publications', ['post_id' => $post->id]);
    }

    public function test_prikaz_ulozi_zhrnutie_titulkov_z_buffera(): void
    {
        $this->fakeSummary("• Prvá myšlienka\n• Druhá myšlienka");
        $post = Post::factory()->create(['body' => str_repeat('slovo ', 200), 'video_id' => 'video123']);
        BufferPublication::create(['post_id' => $post->id, 'canal_id' => $post->canal_id, 'slot_at' => now()]);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->andReturn(str_repeat('titulky ', 200));

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
        $post = Post::factory()->create(['body' => str_repeat('Dlhý popis ', 300), 'video_id' => 'video123']);
        BufferPublication::create(['post_id' => $post->id, 'canal_id' => $post->canal_id, 'slot_at' => now()]);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->once()->andReturn(null);

        $this->artisan('posts:summarize')->assertSuccessful();

        $post->refresh();
        $this->assertNull($post->summary);
        $this->assertNotNull($post->summary_generated_at);
        $this->artisan('posts:summarize')->assertSuccessful();
        $fake->assertNothingSent();
    }

    public function test_zmena_popisu_zhrnutie_zahodi(): void
    {
        $post = Post::factory()->create(['body' => str_repeat('slovo ', 200), 'video_id' => 'video123']);
        BufferPublication::create(['post_id' => $post->id, 'canal_id' => $post->canal_id, 'slot_at' => now()]);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->andReturn(str_repeat('titulky ', 200));
        $post->forceFill(['summary' => 'Staré', 'summary_generated_at' => now()])->saveQuietly();

        $post->update(['body' => str_repeat('iné ', 200)]);

        $post->refresh();
        $this->assertNull($post->summary);
        $this->assertNull($post->summary_generated_at);
    }

    public function test_limit_oreze_titulky_a_nepouzije_popis(): void
    {
        Setting::set(PostSummarizer::SETTING_MAX_TOKENS, 1000);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->andReturn(str_repeat('áno ', 500));
        $fake = $this->fakeSummary('Výsledok');
        $post = Post::factory()->create(['body' => 'TAJNY_POPIS', 'video_id' => 'video123']);
        $service = app(PostSummarizer::class);
        $this->assertSame('Výsledok', $service->summarize($post));
        $fake->chat()->assertSent(function (string $method, array $parameters): bool {
            $input = $parameters['messages'][1]['content'];
            $this->assertStringNotContainsString('TAJNY_POPIS', $input);
            $this->assertLessThanOrEqual(1000, strlen(substr($input, strlen("Prepis reči:\n"))));
            $this->assertTrue(mb_check_encoding($input, 'UTF-8'));
            $this->assertStringContainsString('400 až 550 slov', $parameters['messages'][0]['content']);

            return true;
        });
    }

    public function test_video_mimo_buffera_sa_automaticky_nezhrna(): void
    {
        $fake = OpenAI::fake([]);
        $post = Post::factory()->create(['video_id' => 'video123']);
        $this->artisan('posts:summarize')->assertSuccessful();
        $this->assertNull($post->fresh()->summary_generated_at);
        $fake->assertNothingSent();
    }
}
