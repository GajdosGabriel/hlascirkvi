<?php

namespace Tests\Feature;

use App\Services\EventPortal\EventPortalClient;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EventPortalClientProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
        RateLimiter::clear('eventportal:outbound');
        config([
            'eventportal.url' => 'https://event.example.test',
            'eventportal.max_tags' => 3,
            'eventportal.outbound_limit' => 120,
        ]);
    }

    public function test_event_filters_are_bounded_and_canonical_before_the_request(): void
    {
        Http::fake([
            '*' => Http::response(['data' => [], 'meta' => ['total' => 0]]),
        ]);

        (new EventPortalClient)->events([
            'tags' => 'put,hudba,put,NEPLATNY!,divadlo,viera',
            'municipality' => '../bratislava',
            'search' => str_repeat('x', 150),
            'injected' => 'ignored',
        ]);

        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $query['tags'] === 'divadlo,hudba,put'
                && ! isset($query['municipality'], $query['injected'])
                && strlen($query['search']) === 100;
        });
    }

    public function test_tag_order_uses_the_same_cached_response(): void
    {
        Http::fake([
            '*' => Http::response(['data' => [], 'meta' => ['total' => 0]]),
        ]);

        $client = new EventPortalClient;
        $client->events(['tags' => 'put,hudba']);
        $client->events(['tags' => 'hudba,put']);

        Http::assertSentCount(1);
    }

    public function test_shared_outbound_limit_stops_new_cache_misses(): void
    {
        config(['eventportal.outbound_limit' => 1]);
        Http::fake([
            '*' => Http::response(['data' => [], 'meta' => ['total' => 0]]),
        ]);

        $client = new EventPortalClient;
        $client->events(['search' => 'prvy']);
        $client->events(['search' => 'druhy']);

        Http::assertSentCount(1);
    }

    public function test_filter_combinations_cannot_use_up_the_whole_outbound_budget(): void
    {
        config(['eventportal.filter_limit' => 1]);
        Http::fake([
            '*' => Http::response(['data' => [], 'meta' => ['total' => 0]]),
        ]);

        $client = new EventPortalClient;
        $client->events(['tags' => 'hudba,put']);
        $client->events(['tags' => 'divadlo,viera']);
        $client->events(['search' => 'koncert']);

        Http::assertSentCount(1);

        // Jeden štítok, obec ani detail pod menší strop nespadajú.
        $client->events(['tags' => 'hudba']);
        $client->events(['municipality' => 'nitra']);
        $client->event(15);

        Http::assertSentCount(4);
    }

    public function test_deadlock_on_the_outbound_counter_does_not_break_the_page(): void
    {
        Http::fake(['*' => Http::response(['id' => 15, 'title' => 'Púť'])]);

        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(false);
        RateLimiter::shouldReceive('hit')
            ->andThrow(new QueryException('mysql', 'insert ignore into `cache`', [], new \PDOException(
                'SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock'
            )));

        $this->assertSame(15, (new EventPortalClient)->event(15)?->id());
        Http::assertSentCount(1);
    }

    public function test_event_detail_is_503_not_404_when_portal_fails_without_a_stale_copy(): void
    {
        Http::fake(['*' => Http::response('', 429)]);

        $this->get('/akcie/15')
            ->assertStatus(503)
            ->assertHeader('Retry-After', 60);
    }

    public function test_event_detail_stays_404_when_portal_says_the_event_is_gone(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Not found'], 404)]);

        $this->get('/akcie/15')->assertStatus(404);
    }

    public function test_public_event_list_has_a_per_ip_rate_limit(): void
    {
        $route = Route::getRoutes()->getByName('akcie.index');

        $this->assertNotNull($route);
        $this->assertContains('throttle:30,1', $route->gatherMiddleware());
    }
}
