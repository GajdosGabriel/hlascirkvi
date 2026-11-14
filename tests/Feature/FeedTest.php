<?php

namespace Tests\Feature;

use App\Enums\PostSection;
use App\Models\Canal;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Cache::flush();
    }

    /** Odpoveď ako SimpleXML s registrovaným menným priestorom Atomu. */
    protected function atom(\Illuminate\Testing\TestResponse $response): \SimpleXMLElement
    {
        $response->assertOk();
        $this->assertStringStartsWith('application/atom+xml', $response->headers->get('Content-Type'));

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->getContent());
        libxml_use_internal_errors($previous);

        $this->assertNotFalse($xml, 'Feed nie je platné XML.');
        $xml->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');

        return $xml;
    }

    public function test_feed_uvodnej_stranky_nesie_jej_vyber_prispevkov(): void
    {
        $canal = Canal::factory()->create(['title' => 'Kanál & spol.']);

        $front = Post::factory()->create([
            'canal_id' => $canal->id,
            'title' => 'Kázeň o nádeji',
            'summary' => 'Krátke zhrnutie kázne.',
            // Bez času zhrnutia ho Post::updating pri dopĺňaní slugu zahodí.
            'summary_generated_at' => now(),
            'published_at' => now()->subHour(),
        ]);
        $newer = Post::factory()->create([
            'canal_id' => $canal->id,
            'title' => 'Novší príspevok',
            'summary' => null,
            'body' => '<p>Text <strong>bez</strong> značiek' . "\x01" . '.</p>',
            'published_at' => now(),
        ]);
        $live = Post::factory()->section(PostSection::Live)->create(['title' => 'Prenos naživo']);
        $waiting = Post::factory()->unpublished()->create(['title' => 'Čaká v bufferi']);
        $gone = Post::factory()->create(['title' => 'Zmiznuté video', 'video_available' => false]);

        $xml = $this->atom($this->get('/feed.xml'));

        $titles = array_map('strval', $xml->xpath('//a:entry/a:title'));
        $this->assertSame(['Novší príspevok', 'Kázeň o nádeji'], $titles);

        [$entry] = $xml->xpath('//a:entry[a:title="Kázeň o nádeji"]');
        $entry->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
        $this->assertSame('Kanál & spol.', (string) $entry->xpath('a:author/a:name')[0]);
        $this->assertSame('Krátke zhrnutie kázne.', (string) $entry->xpath('a:summary')[0]);
        $this->assertStringEndsWith(
            "/post/{$front->id}/{$front->slug}",
            (string) $entry->xpath('a:link/@href')[0]
        );
        $this->assertNotEmpty((string) $entry->xpath('a:updated')[0]);

        [$entry] = $xml->xpath('//a:entry[a:title="Novší príspevok"]');
        $entry->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
        $this->assertSame('Text bez značiek.', (string) $entry->xpath('a:summary')[0]);
    }

    public function test_feed_uvodnej_stranky_ma_najviac_30_poloziek(): void
    {
        Post::factory()->count(32)->create(['canal_id' => Canal::factory()->create()->id]);

        $this->assertCount(30, $this->atom($this->get(route('feed')))->xpath('//a:entry'));
    }

    public function test_feed_kanala_nesie_len_jeho_zverejnene_a_dostupne_prispevky(): void
    {
        $canal = Canal::factory()->create();
        Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Video kanála']);
        // Kanál ukazuje aj príspevky mimo úvodnej stránky.
        Post::factory()->section(PostSection::Live)->create(['canal_id' => $canal->id, 'title' => 'Prenos kanála']);
        Post::factory()->unpublished()->create(['canal_id' => $canal->id, 'title' => 'Čaká v bufferi']);
        Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Zmiznuté video', 'video_available' => false]);
        Post::factory()->create(['title' => 'Cudzí príspevok']);

        $xml = $this->atom($this->get("/organizations/{$canal->id}/feed.xml"));

        $titles = array_map('strval', $xml->xpath('//a:entry/a:title'));
        sort($titles);
        $this->assertSame(['Prenos kanála', 'Video kanála'], $titles);
    }

    public function test_nezverejneny_kanal_feed_nema(): void
    {
        $canal = Canal::factory()->unpublished()->create();

        $this->get(route('organizations.feed', [$canal->id]))->assertNotFound();
    }

    public function test_hlavicka_odkazuje_na_feed(): void
    {
        $canal = Canal::factory()->create();
        Post::factory()->create(['canal_id' => $canal->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="alternate" type="application/atom+xml"', false)
            ->assertSee('/feed.xml"', false);

        $this->get(route('organizations.show', [$canal->id]))
            ->assertOk()
            ->assertSee('<link rel="alternate" type="application/atom+xml"', false)
            ->assertSee("/organizations/{$canal->id}/feed.xml\"", false);
    }
}
