<?php

namespace Tests\Feature;

use App\Enums\CanalIdentityMode;
use App\Models\AiUsage;
use App\Models\Canal;
use App\Models\CanalEnrichment;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\Canals\CanalProfileCompleted;
use App\Services\CanalContactPages;
use App\Services\CanalProfileEnricher;
use App\Services\CanalProfileResearch;
use App\Services\PostSummarizer;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Responses\CreateResponse;
use Tests\TestCase;

class CanalProfileEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        config(['openai.api_key' => 'test-key']);
        Notification::fake();
        $this->mock(CanalContactPages::class)->shouldReceive('read')->andReturn([])->byDefault();
    }

    private function canal(array $attributes = []): Canal
    {
        return Canal::factory()->create($attributes + [
            'identity_mode' => CanalIdentityMode::Organization, 'created_at' => now()->subHours(3),
            'email' => null, 'url_www' => null, 'description' => null, 'phone' => null, 'street' => null,
        ]);
    }

    private function found(string $value): array
    {
        return ['value' => $value, 'source_url' => 'https://example.sk/kontakt', 'evidence' => 'Verejný kontakt organizácie'];
    }

    public function test_fills_missing_fields_preserves_user_data_and_sends_one_email(): void
    {
        $canal = $this->canal(['email' => 'vlastny@example.sk']);
        $owner = User::factory()->create();
        $canal->users()->attach($owner);
        $this->mock(CanalProfileResearch::class)->shouldReceive('research')->once()->andReturn([
            'url_www' => $this->found('https://example.sk/spolocenstvo'),
            'email' => $this->found('iny@example.sk'),
            'description' => $this->found('Spoločenstvo sa venuje pomoci rodinám.'),
        ]);
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->assertSame('vlastny@example.sk', $canal->fresh()->email);
        $this->assertSame('https://example.sk/spolocenstvo', $canal->fresh()->url_www);
        $this->assertArrayHasKey('description', CanalEnrichment::sole()->evidence);
        Notification::assertSentToTimes($owner, CanalProfileCompleted::class, 1);
        Notification::assertSentTo($owner, CanalProfileCompleted::class, function ($mail) use ($owner) {
            $this->assertArrayNotHasKey('email', $mail->changes);
            $html = (string) $mail->toMail($owner)->render();
            $this->assertStringContainsString('Skontrolovať profil', $html);

            return true;
        });
    }

    public function test_waits_two_hours_and_skips_personal_and_hidden_profiles(): void
    {
        $this->canal(['created_at' => now()->subMinutes(119)]);
        $this->canal(['identity_mode' => CanalIdentityMode::Personal]);
        $this->canal(['published' => null]);
        $this->mock(CanalProfileResearch::class)->shouldNotReceive('research');
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->assertDatabaseCount('canal_enrichments', 0);
        Notification::assertNothingSent();
    }

    public function test_rechecks_user_edits_made_during_research(): void
    {
        $canal = $this->canal();
        $this->mock(CanalProfileResearch::class)->shouldReceive('research')->once()->andReturnUsing(function () use ($canal) {
            Canal::find($canal->id)->update(['email' => 'pouzivatel@example.sk']);

            return ['email' => $this->found('ai@example.sk'), 'description' => $this->found('Overený popis.')];
        });
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->assertSame('pouzivatel@example.sk', $canal->fresh()->email);
        $this->assertSame('Overený popis.', $canal->fresh()->description);
        Notification::assertNothingSent();
    }

    public function test_no_result_retries_after_delay_and_stops_after_three_attempts(): void
    {
        $canal = $this->canal();
        $canal->users()->attach(User::factory()->create());
        $this->mock(CanalProfileResearch::class)->shouldReceive('research')->times(3)->andReturn([]);
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->assertNull(CanalEnrichment::sole()->completed_at);
        $this->travel(7)->hours();
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->travel(7)->hours();
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->assertNotNull(CanalEnrichment::sole()->completed_at);
        $this->assertNull($canal->fresh()->email);
        Notification::assertNothingSent();
    }

    public function test_respects_switch_and_shared_monthly_budget(): void
    {
        $this->canal();
        $this->mock(CanalProfileResearch::class)->shouldNotReceive('research');
        Setting::set(CanalProfileEnricher::SETTING_ENABLED, 0);
        $this->artisan('canals:enrich')->assertSuccessful();
        Setting::set(CanalProfileEnricher::SETTING_ENABLED, 1);
        Setting::set(PostSummarizer::SETTING_LIMIT, 0.01);
        AiUsage::record('test', null, 'gpt-4o-mini', 100000, 0);
        $this->artisan('canals:enrich')->assertSuccessful();
    }

    public function test_transient_failure_retries_later_without_marking_complete(): void
    {
        $this->canal();
        $this->mock(CanalProfileResearch::class)->shouldReceive('research')->once()->andThrow(new \RuntimeException('Unavailable'));
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->assertSame(1, CanalEnrichment::sole()->attempts);
        $this->assertNull(CanalEnrichment::sole()->completed_at);
    }

    public function test_research_requires_identity_official_evidence_and_real_search_source(): void
    {
        $service = new CanalProfileResearch;
        $entry = $this->found('info@example.sk') + ['official_source' => true];
        $data = ['identity_match' => true, 'fields' => ['email' => $entry]];
        $this->assertArrayHasKey('email', $service->verifiedFields($data, [$entry['source_url']], ['email']));
        $this->assertSame([], $service->verifiedFields($data, [], ['email']));
        $this->assertSame([], $service->verifiedFields($data, [$entry['source_url']], ['description']));
        $data['identity_match'] = false;
        $this->assertSame([], $service->verifiedFields($data, [$entry['source_url']], ['email']));
        $data['identity_match'] = true;
        $data['fields']['email']['value'] = 'nie je email';
        $this->assertSame([], $service->verifiedFields($data, [$entry['source_url']], ['email']));
    }

    public function test_admin_can_review_saved_changes_and_their_sources(): void
    {
        $canal = $this->canal();
        CanalEnrichment::create([
            'canal_id' => $canal->id, 'completed_at' => now(),
            'changes' => ['email' => 'kontakt@example.sk'],
            'evidence' => ['email' => $this->found('kontakt@example.sk')],
        ]);
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);
        $this->withoutVite()->actingAs($admin)->get('/admin/ai')->assertOk()
            ->assertSee('kontakt@example.sk')->assertSee('https://example.sk/kontakt');
    }

    public function test_failed_mail_is_retried_without_another_search(): void
    {
        $canal = $this->canal();
        $owner = User::factory()->create();
        $canal->users()->attach($owner);
        $this->mock(CanalProfileResearch::class)->shouldReceive('research')->once()
            ->andReturn(['email' => $this->found('info@example.sk')]);
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Mail unavailable'));
        $this->artisan('canals:enrich')->assertSuccessful();
        $this->assertNotNull(CanalEnrichment::sole()->completed_at);
        $this->assertNull(CanalEnrichment::sole()->notification_completed_at);
        Notification::fake();
        $this->artisan('canals:enrich')->assertSuccessful();
        Notification::assertSentToTimes($owner, CanalProfileCompleted::class, 1);
        $this->assertNotNull(CanalEnrichment::sole()->notification_completed_at);
    }

    public function test_responses_search_is_parsed_and_cost_is_recorded(): void
    {
        $canal = $this->canal();
        $entry = $this->found('info@example.sk') + ['official_source' => true];
        $fake = OpenAI::fake([CreateResponse::fake([
            'model' => 'gpt-4.1-mini',
            'output' => [
                ['type' => 'web_search_call', 'id' => 'ws_test', 'status' => 'completed', 'action' => [
                    'type' => 'search', 'query' => 'kontakt', 'sources' => [['type' => 'url', 'url' => $entry['source_url']]],
                ]],
                ['type' => 'message', 'id' => 'msg_test', 'status' => 'completed', 'role' => 'assistant', 'content' => [
                    ['type' => 'output_text', 'text' => json_encode(['identity_match' => true, 'fields' => ['email' => $entry]]), 'annotations' => []],
                ]],
            ],
        ])]);
        $result = app(CanalProfileResearch::class)->research($canal, ['email']);
        $this->assertSame('info@example.sk', $result['email']['value']);
        $this->assertGreaterThanOrEqual(0.01, AiUsage::sole()->cost_usd);
        $fake->responses()->assertSent(function (string $method, array $parameters) {
            $this->assertSame('required', $parameters['tool_choice']);
            $this->assertFalse($parameters['store']);

            return true;
        });
    }

    public function test_explicit_retry_reopens_only_selected_empty_completed_channel(): void
    {
        $canal = $this->canal();
        $other = $this->canal();
        foreach ([$canal, $other] as $item) {
            CanalEnrichment::create(['canal_id' => $item->id, 'completed_at' => now(), 'attempts' => 3, 'changes' => []]);
        }
        $this->mock(CanalProfileResearch::class)->shouldReceive('research')->once()
            ->andReturn(['email' => $this->found('kontakt@example.sk')]);
        $this->artisan('canals:enrich', ['--retry-empty' => true, '--canal' => [$canal->id]])->assertSuccessful();
        $this->assertSame('kontakt@example.sk', $canal->fresh()->email);
        $this->assertNull($other->fresh()->email);
        $this->artisan('canals:enrich', ['--retry-empty' => true, '--canal' => [$canal->id]])->assertSuccessful();
    }

    public function test_follow_up_search_finds_contact_using_first_pass_website(): void
    {
        $canal = $this->canal();
        $response = function (string $field, string $value) {
            $entry = $this->found($value) + ['official_source' => true];

            return CreateResponse::fake([
                'model' => 'gpt-6-luna',
                'output' => [
                    ['type' => 'web_search_call', 'id' => 'ws_test', 'status' => 'completed', 'action' => [
                        'type' => 'search', 'sources' => [['type' => 'url', 'url' => $entry['source_url']]],
                    ]],
                    ['type' => 'message', 'id' => 'msg_test', 'status' => 'completed', 'role' => 'assistant', 'content' => [
                        ['type' => 'output_text', 'text' => json_encode(['identity_match' => true, 'fields' => [$field => $entry]]), 'annotations' => []],
                    ]],
                ],
            ]);
        };
        $fake = OpenAI::fake([$response('url_www', 'https://example.sk'), $response('email', 'kontakt@example.sk')]);
        $service = app(CanalProfileResearch::class);
        $result = $service->research($canal, ['url_www', 'email']);
        $this->assertSame('kontakt@example.sk', $result['email']['value']);
        $this->assertCount(2, $service->diagnostics);
        $fake->responses()->assertSent(function (string $method, array $parameters) {
            $input = json_decode($parameters['input'], true);

            return $input['missing'] === ['email'] && $input['verified_so_far']['url_www']['value'] === 'https://example.sk';
        });
    }

    public function test_direct_contact_page_is_a_valid_source_and_normalizes_phone_spacing(): void
    {
        $canal = $this->canal(['url_www' => 'https://example.sk']);
        $source = 'https://example.sk/kontakt/';
        $this->mock(CanalContactPages::class)->shouldReceive('read')->once()->andReturn([
            ['url' => $source, 'text' => 'Kontakt organizácie: +421 907 817 323'],
        ]);
        $entry = ['value' => "+421\u{00a0}907 817 323\u{200b}", 'source_url' => $source,
            'official_source' => true, 'evidence' => 'Kontakt organizácie'];
        OpenAI::fake([CreateResponse::fake([
            'model' => 'gpt-6-luna',
            'output' => [
                ['type' => 'web_search_call', 'id' => 'ws_test', 'status' => 'completed', 'action' => ['type' => 'search']],
                ['type' => 'message', 'id' => 'msg_test', 'status' => 'completed', 'role' => 'assistant', 'content' => [
                    ['type' => 'output_text', 'text' => json_encode(['identity_match' => true, 'fields' => ['phone' => $entry]]), 'annotations' => []],
                ]],
            ],
        ])]);
        $result = app(CanalProfileResearch::class)->research($canal, ['phone']);
        $this->assertSame('+421 907 817 323', $result['phone']['value']);
    }

    private function searchResponse(array $fields, array $sources): CreateResponse
    {
        return CreateResponse::fake([
            'model' => 'gpt-6-luna',
            'output' => [
                ['type' => 'web_search_call', 'id' => 'ws_test', 'status' => 'completed', 'action' => [
                    'type' => 'search', 'sources' => array_map(fn ($url) => ['type' => 'url', 'url' => $url], $sources),
                ]],
                ['type' => 'message', 'id' => 'msg_test', 'status' => 'completed', 'role' => 'assistant', 'content' => [
                    ['type' => 'output_text', 'text' => json_encode(['identity_match' => true, 'fields' => $fields]), 'annotations' => []],
                ]],
            ],
        ]);
    }

    public function test_rejected_source_is_read_and_researched_again_before_acceptance(): void
    {
        $canal = $this->canal();
        $fields = [
            'url_www' => ['value' => 'https://example.sk/', 'source_url' => 'https://example.sk/kontakt', 'official_source' => true, 'evidence' => 'Oficiálny web'],
            'email' => $this->found('kontakt@example.sk') + ['official_source' => true],
        ];
        $this->mock(CanalContactPages::class)->shouldReceive('read')->once()->with('https://example.sk/')->andReturn([
            ['url' => 'https://example.sk/kontakt', 'text' => 'Oficiálny kontakt: kontakt@example.sk'],
        ]);
        OpenAI::fake([
            $this->searchResponse($fields, ['https://example.sk/']),
            $this->searchResponse($fields, ['https://example.sk/']),
        ]);
        $service = app(CanalProfileResearch::class);
        $result = $service->research($canal, ['url_www', 'email']);
        $this->assertCount(2, $result);
        $this->assertSame([], $service->diagnostics[0]['accepted']);
        $this->assertSame(['url_www', 'email'], $service->diagnostics[1]['accepted']);
    }

    public function test_follow_up_failure_keeps_verified_results(): void
    {
        $canal = $this->canal();
        $entry = $this->found('info@example.sk') + ['official_source' => true];
        OpenAI::fake([$this->searchResponse(['email' => $entry], [$entry['source_url']]), new \RuntimeException('Unavailable')]);
        $service = app(CanalProfileResearch::class);
        $result = $service->research($canal, ['email', 'phone']);
        $this->assertSame('info@example.sk', $result['email']['value']);
        $this->assertSame('follow_up_failed', $service->diagnostics[1]['status']);
    }
}
