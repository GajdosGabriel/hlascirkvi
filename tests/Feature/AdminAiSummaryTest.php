<?php

namespace Tests\Feature;

use App\Models\AiUsage;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Services\PostSummarizer;
use App\Services\YoutubeCaptions;
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
        $this->mock(YoutubeCaptions::class)->shouldReceive('text')->andReturn(str_repeat('titulky ', 200));
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
            ->put('/admin/ai', ['enabled' => '1', 'batch' => 10, 'limit' => 2.5, 'max_tokens' => 15000])
            ->assertRedirect();

        $summarizer = app(PostSummarizer::class);
        $this->assertTrue($summarizer->enabled());
        $this->assertSame(10, $summarizer->batchSize());
        $this->assertSame(2.5, $summarizer->monthlyLimit());
        $this->assertSame(15000, $summarizer->maxTokens());
        $this->assertSame('a4', $summarizer->length());
    }

    public function test_vypnute_zhrnutia_prikaz_nespusti(): void
    {
        $fake = OpenAI::fake([]);
        Post::factory()->create(['body' => str_repeat('slovo ', 200)]);

        $this->artisan('posts:summarize')->assertSuccessful();

        $fake->assertNothingSent();
        $this->assertDatabaseCount('ai_usages', 0);
    }

    public function test_usage_filters_apply_to_totals_groups_and_paginated_calls_only(): void
    {
        $this->travelTo(now()->startOfMonth()->addDays(10)->startOfDay());
        $attributes = ['model' => 'model-a', 'prompt_tokens' => 100, 'completion_tokens' => 20, 'cost_usd' => 0.01];
        for ($i = 0; $i < 26; $i++) {
            AiUsage::create($attributes + ['feature' => 'canal_enrichment']);
        }
        AiUsage::create($attributes + ['feature' => 'guest_reply']);
        AiUsage::create(array_replace($attributes, ['feature' => 'canal_enrichment', 'model' => 'model-b']));
        $old = AiUsage::create($attributes + ['feature' => 'canal_enrichment']);
        $old->forceFill(['created_at' => now()->subDays(8)])->save();

        $url = '/admin/ai?days=7&feature=canal_enrichment&model=model-a';
        $response = $this->actingAs($this->admin())->get($url)->assertOk();
        $response->assertViewHas('periodTotals', fn ($total) => (int) $total->calls === 26 && (int) $total->prompt === 2600 && abs($total->cost - 0.26) < 0.00001);
        $response->assertViewHas('month', fn ($total) => (int) $total->calls === 29);
        $response->assertViewHas('daily', fn ($rows) => $rows->count() === 1 && (int) $rows->first()->calls === 26);
        $response->assertViewHas('groups', fn ($groups) => $groups->every(fn ($group) => $group['rows']->count() === 1 && (int) $group['rows']->first()->calls === 26));
        $response->assertViewHas('recent', fn ($rows) => $rows->total() === 26 && $rows->count() === 25 && str_contains($rows->nextPageUrl(), 'feature=canal_enrichment'));
        $this->get($url.'&page=2')->assertOk()->assertViewHas('recent', fn ($rows) => $rows->count() === 1);
        $this->get('/admin/ai?feature=missing')->assertOk()->assertViewHas('periodTotals', fn ($total) => (int) $total->calls === 0);
        $this->getJson('/admin/ai?days=10000')->assertUnprocessable();
        $this->travelBack();
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
