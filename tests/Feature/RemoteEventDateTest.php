<?php

namespace Tests\Feature;

use App\Services\EventPortal\RemoteEvent;
use Tests\TestCase;

class RemoteEventDateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => 'Europe/Bratislava']);
    }

    public function test_multi_day_all_day_event_has_no_time(): void
    {
        // Podujatie 223 z portálu — 18. – 20. 9. 2026 bez času.
        $event = new RemoteEvent([
            'start_at' => '2026-09-17T22:00:00.000000Z',
            'end_at' => '2026-09-20T21:59:59.000000Z',
            'date_range_label' => '17. 09. 2026 22:00 - 20. 09. 2026 21:59',
        ]);

        $this->assertTrue($event->isAllDay());
        $this->assertTrue($event->isMultiDay());
        $this->assertNull($event->timeLabel());
        $this->assertSame('Piatok – nedeľa, 18. – 20. septembra 2026', $event->dateLabel());
        $this->assertSame('18. 09. 2026 - 20. 09. 2026', $event->dateRangeLabel());
    }

    public function test_single_day_all_day_event(): void
    {
        $event = new RemoteEvent([
            'start_at' => '2026-09-04T22:00:00Z',
            'end_at' => '2026-09-05T21:59:59Z',
        ]);

        $this->assertTrue($event->isAllDay());
        $this->assertSame('celý deň', $event->timeLabel());
        $this->assertSame('Sobota, 5. septembra 2026', $event->dateLabel());
        $this->assertSame('05. 09. 2026, celý deň', $event->dateRangeLabel());
    }

    public function test_timed_event_keeps_local_time(): void
    {
        $event = new RemoteEvent([
            'start_at' => '2026-09-07T14:00:00Z',
            'end_at' => '2026-09-07T15:30:00Z',
        ]);

        $this->assertFalse($event->isAllDay());
        $this->assertSame('16:00 – 17:30', $event->timeLabel());
        $this->assertSame('07. 09. 2026 16:00 - 17:30', $event->dateRangeLabel());
    }

    public function test_multi_day_range_across_months(): void
    {
        $event = new RemoteEvent([
            'start_at' => '2026-09-29T22:00:00Z',
            'end_at' => '2026-10-02T21:59:59Z',
        ]);

        $this->assertSame('Streda – piatok, 30. septembra – 2. októbra 2026', $event->dateLabel());
    }
}
