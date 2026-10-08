<?php

namespace Tests\Feature;

use App\Services\EventPortal\EventPortalClient;
use App\View\Components\UpcomingEvents;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class UpcomingEventsCardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
        RateLimiter::clear('eventportal:outbound');
        config([
            'app.timezone' => 'Europe/Bratislava',
            'eventportal.url' => 'https://event.example.test',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-10-08 09:00', 'Europe/Bratislava'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @param array<int, array<string, mixed>> $events */
    protected function fakePortal(array $events): void
    {
        Http::fake(['*' => Http::response(['data' => $events, 'meta' => ['total' => count($events)]])]);
    }

    /** @return array<string, mixed> */
    protected function event(int $id, string $startUtc): array
    {
        return ['id' => $id, 'name' => 'Akcia '.$id, 'slug' => 'akcia-'.$id, 'start_at' => $startUtc];
    }

    public function test_card_shows_only_the_nearest_day_and_skips_running_events(): void
    {
        $this->fakePortal([
            $this->event(1, '2026-10-05T08:00:00Z'), // prebieha, začalo skôr
            $this->event(2, '2026-10-10T16:00:00Z'),
            $this->event(3, '2026-10-10T08:00:00Z'),
            $this->event(4, '2026-10-11T08:00:00Z'),
        ]);

        $card = new UpcomingEvents(new EventPortalClient);

        $this->assertTrue($card->shouldRender());
        $this->assertSame([3, 2], $card->events->map->id()->all());
        $this->assertSame('Sobota 10. októbra', $card->dayLabel());
    }

    public function test_events_over_the_limit_are_folded_behind_a_toggle(): void
    {
        $this->fakePortal(array_map(
            fn (int $id) => $this->event($id, '2026-10-08T1'.$id.':00:00Z'),
            [1, 2, 3, 4, 5]
        ));

        $html = $this->blade('<x-upcoming-events />');

        $html->assertSee('Dnes');
        $html->assertSeeInOrder(['Akcia 3', 'type="checkbox"', 'Akcia 4', 'Akcia 5', 'Zobraziť ďalšie (2)', 'Všetky akcie'], false);
    }

    public function test_card_is_hidden_without_events(): void
    {
        $this->fakePortal([]);

        $this->assertSame('', trim((string) $this->blade('<x-upcoming-events />')));
    }
}
