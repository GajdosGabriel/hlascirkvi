<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Services\VideoUpload;
use App\Services\Youtube\VideoImportSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakesYoutube;
use Tests\TestCase;

class VideoImportScheduleTest extends TestCase
{
    use FakesYoutube, RefreshDatabase;

    private const CHANNEL = 'UCtWheHmWwuokUASxXus4NBw';

    public function test_weekly_channel_skips_other_days_and_runs_only_once_on_its_day(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(16, 24));
        $canal = Canal::factory()->create(['youtube_channel' => self::CHANNEL, 'import_day' => 2]);
        $this->fakeYoutube(['playlistItems' => ['items' => []]]);
        (new VideoUpload)->handle();
        $this->assertCount(0, $this->youtubeRequests('playlistItems'));

        $this->travel(1)->days();
        (new VideoUpload)->handle();
        (new VideoUpload)->handle();
        $this->assertCount(1, $this->youtubeRequests('playlistItems'));
        $this->assertSame('2026-10-06 16:24:00', $canal->fresh()->video_check_next_at->toDateTimeString());
        $this->assertNotNull($canal->fresh()->video_check_succeeded_at);
    }

    public function test_missed_weekly_channel_is_caught_up_next_day(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 29)->setTime(16, 24));
        $canal = Canal::factory()->create([
            'youtube_channel' => self::CHANNEL, 'import_day' => 1,
            'video_check_next_at' => now()->subDay(),
        ]);
        $this->fakeYoutube(['playlistItems' => ['items' => []]]);
        (new VideoUpload)->handle();
        $this->assertCount(1, $this->youtubeRequests('playlistItems'));
        $this->assertSame('2026-10-05 16:24:00', $canal->fresh()->video_check_next_at->toDateTimeString());
    }

    public function test_quota_failure_does_not_mark_success_and_retries_tomorrow(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(16, 24));
        $canal = Canal::factory()->create(['youtube_channel' => self::CHANNEL, 'import_day' => 1]);
        $failed = true;
        $this->fakeYoutube(['playlistItems' => function () use (&$failed) {
            return $failed ? $this->youtubeError(403, 'quotaExceeded') : ['items' => []];
        }]);
        (new VideoUpload)->handle();
        $this->assertNull($canal->fresh()->video_check_succeeded_at);
        $this->assertNotNull($canal->fresh()->video_check_error);
        (new VideoUpload)->handle();
        $this->assertCount(1, $this->youtubeRequests('playlistItems'));

        $failed = false;
        $this->travel(1)->days();
        (new VideoUpload)->handle();
        $this->assertCount(2, $this->youtubeRequests('playlistItems'));
        $this->assertNull($canal->fresh()->video_check_error);
        $this->assertNotNull($canal->fresh()->video_check_succeeded_at);
    }

    public function test_daily_sources_keep_daily_and_sunday_checks_and_manual_check_bypasses_weekday(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(16, 24));
        $daily = Canal::factory()->create(['youtube_channel' => self::CHANNEL]);
        $weekly = Canal::factory()->create(['youtube_channel' => self::CHANNEL, 'import_day' => 4]);
        $this->fakeYoutube(['playlistItems' => ['items' => []]]);
        (new VideoUpload)->handle();
        (new VideoUpload)->handle();
        $this->assertCount(2, $this->youtubeRequests('playlistItems'));
        $this->assertNull($weekly->fresh()->video_check_succeeded_at);
        (new VideoUpload)->handle($weekly->id);
        $this->assertNotNull($weekly->fresh()->video_check_succeeded_at);
    }

    public function test_automatic_day_chooses_least_loaded_enabled_day_including_sunday(): void
    {
        foreach (range(1, 6) as $day) {
            Canal::factory()->create(['import_day' => $day]);
        }
        Canal::factory()->create(['import_day' => 0, 'post_section' => 'paused']);
        $this->assertSame(0, VideoImportSchedule::suggestedDay());
    }
}
