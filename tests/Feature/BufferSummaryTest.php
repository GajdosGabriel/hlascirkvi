<?php

namespace Tests\Feature;

use App\Models\AiUsage;
use App\Models\Post;
use App\Models\Setting;
use App\Services\Buffer;
use App\Services\PostSummarizer;
use App\Services\YoutubeCaptions;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class BufferSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        config(['openai.api_key' => 'test-key']);
        Setting::set(PostSummarizer::SETTING_ENABLED, 1);
    }

    public function test_selected_video_is_checked_before_publication(): void
    {
        OpenAI::fake([CreateResponse::fake(['choices' => [['message' => ['content' => 'Najzaujímavejšia myšlienka']]]])]);
        $post = Post::factory()->unpublished()->create(['video_id' => 'selected']);
        $waiting = Post::factory()->unpublished()->create(['canal_id' => $post->canal_id, 'video_id' => 'waiting']);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->once()->with('selected')
            ->andReturnUsing(function () use ($post) {
                $this->assertNull($post->fresh()->published_at);
                $this->assertDatabaseMissing('buffer_publications', ['post_id' => $post->id]);

                return str_repeat('titulky ', 200);
            });
        $this->assertSame($post->id, app(Buffer::class)->handler(true)->id);
        $this->assertSame('Najzaujímavejšia myšlienka', $post->fresh()->summary);
        $this->assertNotNull($post->fresh()->published_at);
        $this->assertNull($waiting->fresh()->summary_generated_at);
        $this->assertNull($waiting->fresh()->published_at);
        $this->artisan('posts:summarize')->assertSuccessful();
    }

    public function test_missing_captions_do_not_prevent_publication(): void
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

    public function test_guards_preserve_publication_without_ai_calls(): void
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

    public function test_caption_failure_does_not_stop_publication(): void
    {
        $post = Post::factory()->unpublished()->create(['video_id' => 'failed']);
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->once()->andThrow(new \RuntimeException('Unavailable'));
        app(Buffer::class)->handler(true);
        $this->assertNotNull($post->fresh()->published_at);
        $this->assertNull($post->fresh()->summary_generated_at);
        $this->assertDatabaseHas('buffer_publications', ['post_id' => $post->id]);
    }
}
