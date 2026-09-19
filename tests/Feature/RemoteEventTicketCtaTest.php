<?php

namespace Tests\Feature;

use App\Services\EventPortal\RemoteEvent;
use Tests\TestCase;

class RemoteEventTicketCtaTest extends TestCase
{
    public function test_cta_from_portal_is_passed_through_with_registration_link(): void
    {
        config(['eventportal.url' => 'https://event.hlascirkvi.sk']);

        $event = new RemoteEvent([
            'id' => 123,
            'slug' => 'koncert',
            'ticket_cta' => ['kind' => 'buy', 'label' => 'Kúpiť lístok'],
        ]);

        $this->assertSame(['kind' => 'buy', 'label' => 'Kúpiť lístok'], $event->ticketCta());
        $this->assertSame('https://event.hlascirkvi.sk/akcie/123/koncert#registracia', $event->ticketUrl());
    }

    public function test_missing_or_unknown_cta_shows_no_button(): void
    {
        foreach ([null, 'buy', [], ['kind' => 'donate', 'label' => 'Darovať']] as $cta) {
            $this->assertNull((new RemoteEvent(['ticket_cta' => $cta]))->ticketCta());
        }

        $this->assertNull((new RemoteEvent([]))->ticketCta());
    }

    public function test_empty_label_falls_back_to_slovak_text(): void
    {
        $event = new RemoteEvent(['ticket_cta' => ['kind' => 'reserve', 'label' => '']]);

        $this->assertSame('Rezervovať', $event->ticketCta()['label']);
    }
}
