<?php

namespace Tests\Feature;

use App\Models\AiUsage;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Services\PostSummarizer;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class AdminAiSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
        config(['openai.api_key' => 'test-key']);
    }

    protected function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);

        return $admin;
    }

    protected function fakeSummary(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'model' => 'gpt-4o-mini',
                'choices' => [['message' => ['content' => '• Myšlienka']]],
                'usage' => ['prompt_tokens' => 1000, 'completion_tokens' => 200, 'total_tokens' => 1200],
            ]),
        ]);
    }

    public function test_bezny_pouzivatel_sa_na_stranku_nedostane(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/ai')->assertRedirect('/');
    }

    public function test_admin_vidi_stranku_a_ulozi_nastavenie(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/ai')->assertOk()->assertSee('Automatické zhrnutia');

        $this->actingAs($admin)
            ->put('/admin/ai', ['enabled' => '1', 'batch' => 10, 'limit' => 2.5])
            ->assertRedirect();

        $summarizer = app(PostSummarizer::class);
        $this->assertTrue($summarizer->enabled());
        $this->assertSame(10, $summarizer->batchSize());
        $this->assertSame(2.5, $summarizer->monthlyLimit());
    }

    public function test_vypnute_zhrnutia_prikaz_nespusti(): void
    {
        $fake = OpenAI::fake([]);
        Post::factory()->create(['body' => str_repeat('slovo ', 200)]);

        $this->artisan('posts:summarize')->assertSuccessful();

        $fake->assertNothingSent();
        $this->assertDatabaseCount('ai_usages', 0);
    }

    public function test_vynutene_zhrnutie_zapise_spotrebu_aj_pri_vypnutych_davkach(): void
    {
        $this->fakeSummary();
        $post = Post::factory()->create(['body' => str_repeat('slovo ', 200)]);

        $this->actingAs($this->admin())
            ->post('/admin/ai/summarize', ['post' => url("/post/{$post->id}/nieco")])
            ->assertRedirect();

        $this->assertSame('• Myšlienka', $post->fresh()->summary);

        $usage = AiUsage::sole();
        $this->assertSame(1000, $usage->prompt_tokens);
        $this->assertSame(200, $usage->completion_tokens);
        // 1000 × 0,15 + 200 × 0,60 za milión tokenov.
        $this->assertEqualsWithDelta(0.00027, $usage->cost_usd, 0.000001);
    }

    public function test_vycerpany_limit_zastavi_davku(): void
    {
        $fake = OpenAI::fake([]);
        Setting::set(PostSummarizer::SETTING_ENABLED, 1);
        Setting::set(PostSummarizer::SETTING_LIMIT, 0.01);
        AiUsage::record(PostSummarizer::FEATURE, null, 'gpt-4o-mini', 100000, 0);
        Post::factory()->create(['body' => str_repeat('slovo ', 200)]);

        $this->artisan('posts:summarize')->assertSuccessful();

        $fake->assertNothingSent();
    }
}
