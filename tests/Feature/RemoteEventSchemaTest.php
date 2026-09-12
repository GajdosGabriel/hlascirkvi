<?php

namespace Tests\Feature;

use App\Services\EventPortal\RemoteEvent;
use Tests\TestCase;

class RemoteEventSchemaTest extends TestCase
{
    public function test_known_prices_include_free_admission_and_ticket_link(): void
    {
        config(['eventportal.url' => 'https://event.hlascirkvi.sk']);

        foreach (['0.00', '12.50'] as $price) {
            $event = new RemoteEvent([
                'id' => 123,
                'slug' => 'koncert',
                'start_at' => '2099-01-01T18:00:00Z',
                'price_amount' => $price,
                'price_currency' => 'EUR',
                'tickets_enabled' => true,
            ]);

            $offer = $event->schemaOrg()['offers'];
            $this->assertSame((float) $price, $offer['price']);
            $this->assertSame('EUR', $offer['priceCurrency']);
            $this->assertSame('https://event.hlascirkvi.sk/akcie/123/koncert', $offer['url']);
            $this->assertArrayNotHasKey('availability', $offer);
        }
    }

    public function test_missing_or_invalid_prices_do_not_claim_free_admission(): void
    {
        foreach ([null, '', 'unknown', -1] as $price) {
            $event = new RemoteEvent(['price_amount' => $price]);
            $this->assertArrayNotHasKey('offers', $event->schemaOrg());
        }
    }

    public function test_source_article_and_organizer_are_not_ticket_url_or_performer(): void
    {
        $event = new RemoteEvent([
            'price_amount' => 5,
            'website' => 'https://example.com/article',
            'canal' => ['name' => 'Organizator'],
        ]);

        $schema = $event->schemaOrg();
        $this->assertArrayNotHasKey('url', $schema['offers']);
        $this->assertArrayNotHasKey('performer', $schema);
        $this->assertSame('Organizator', $schema['organizer']['name']);
    }

    public function test_past_event_does_not_offer_tickets_hidden_on_the_page(): void
    {
        $event = new RemoteEvent([
            'start_at' => '2000-01-01T18:00:00Z',
            'price_amount' => 10,
            'tickets_enabled' => true,
        ]);

        $this->assertArrayNotHasKey('offers', $event->schemaOrg());
    }

    public function test_organizer_always_has_a_url(): void
    {
        $event = new RemoteEvent([
            'id' => 123,
            'slug' => 'koncert',
            'canal' => ['name' => 'Organizator', 'website' => 'https://organizator.sk'],
        ]);

        $this->assertSame('https://organizator.sk', $event->schemaOrg()['organizer']['url']);

        // Kanál bez webu (alebo s nepoužiteľnou adresou) nesmie nechať `url`
        // prázdnu — Search Console to hlási ako chybu štruktúrovaných dát.
        foreach ([null, '', '   ', 'www.organizator.sk', 'javascript:alert(1)'] as $website) {
            $event = new RemoteEvent([
                'id' => 123,
                'slug' => 'koncert',
                'canal' => ['name' => 'Organizator', 'website' => $website],
            ]);

            $this->assertNull($event->organizerWebsite());
            $this->assertSame(
                route('event.show', [123, 'koncert']),
                $event->schemaOrg()['organizer']['url']
            );
        }
    }
}
