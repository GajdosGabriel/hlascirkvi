<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class RssCanalCacheTest extends TestCase
{
    private const FEED = '<?xml version="1.0"?><rss><channel><title>TK KBS</title><item><title>%s</title></item></channel></rss>';

    protected function setUp(): void
    {
        parent::setUp();

        Sleep::fake();
    }

    public function test_feed_is_cached_for_fifteen_minutes_per_canal(): void
    {
        Http::fake(['www.tkkbs.sk/*' => Http::sequence()
            ->push(sprintf(self::FEED, 'Prvá správa'))
            ->push(sprintf(self::FEED, 'Druhá správa'))
            ->push(sprintf(self::FEED, 'Tretia správa'))]);

        $this->getJson(route('api.rss', 'domov'))->assertOk()->assertJsonPath('channel.item.title', 'Prvá správa');
        $this->getJson(route('api.rss', 'domov'))->assertOk()->assertJsonPath('channel.item.title', 'Prvá správa');
        Http::assertSentCount(1);

        // Iný kanál má vlastnú cache.
        $this->getJson(route('api.rss', 'press'))->assertOk()->assertJsonPath('channel.item.title', 'Druhá správa');
        Http::assertSentCount(2);

        $this->travel(16)->minutes();
        $this->getJson(route('api.rss', 'domov'))->assertOk()->assertJsonPath('channel.item.title', 'Tretia správa');
        Http::assertSentCount(3);
    }

    public function test_outage_returns_last_successful_response(): void
    {
        Http::fake(['www.tkkbs.sk/*' => Http::sequence()
            ->push(sprintf(self::FEED, 'Posledná dobrá'))
            ->whenEmpty(Http::response('Service Unavailable', 503))]);

        $this->getJson(route('api.rss', 'zahranicie'))->assertOk();

        $this->travel(16)->minutes();
        $this->getJson(route('api.rss', 'zahranicie'))->assertOk()->assertJsonPath('channel.item.title', 'Posledná dobrá');
    }

    public function test_unreadable_feed_falls_back_to_last_successful_response(): void
    {
        Http::fake(['www.tkkbs.sk/*' => Http::sequence()
            ->push(sprintf(self::FEED, 'Posledná dobrá'))
            ->push('<html>chyba')]);

        $this->getJson(route('api.rss', 'domov'))->assertOk();

        $this->travel(16)->minutes();
        $this->getJson(route('api.rss', 'domov'))->assertOk()->assertJsonPath('channel.item.title', 'Posledná dobrá');
    }

    public function test_outage_without_previous_response_returns_502(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->getJson(route('api.rss', 'domov'))->assertStatus(502)->assertJsonPath('message', 'RSS kanál je nedostupný.');

        Http::fake(['www.tkkbs.sk/*' => Http::response('Bad Gateway', 502)]);
        $this->getJson(route('api.rss', 'press'))->assertStatus(502);
    }
}
