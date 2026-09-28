<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Services\VideoUpload;
use App\Services\VideoUploadByUserName;
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

    public function test_name_search_pages_resume_tomorrow_in_same_window_and_then_use_overlap(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(6, 55));
        config(['youtube.name_search.max_pages_per_canal' => 1]);
        $canal = Canal::factory()->create(['import_day' => 1]);
        $queries = [];
        $this->fakeYoutube(['search' => function ($query) use (&$queries) {
            $queries[] = $query;

            return isset($query['pageToken']) ? ['items' => []] : ['items' => [], 'nextPageToken' => 'page2'];
        }]);
        (new VideoUploadByUserName)->handle();
        $this->assertSame('page2', $canal->fresh()->name_search_page_token);
        $this->assertNull($canal->fresh()->video_check_succeeded_at);
        (new VideoUploadByUserName)->handle();
        $this->assertCount(1, $queries);
        $this->assertSame('date', $queries[0]['order']);
        $this->assertSame('video', $queries[0]['type']);
        $this->assertSame('50', $queries[0]['maxResults']);

        $this->travel(1)->days();
        (new VideoUploadByUserName)->handle();
        $this->assertCount(2, $queries);
        $this->assertSame('page2', $queries[1]['pageToken']);
        $this->assertSame($queries[0]['publishedAfter'], $queries[1]['publishedAfter']);
        $this->assertSame($queries[0]['publishedBefore'], $queries[1]['publishedBefore']);
        $canal->refresh();
        $this->assertNull($canal->name_search_page_token);
        $this->assertNotNull($canal->video_check_succeeded_at);
        $completed = $canal->name_search_completed_until->copy();

        $this->travelTo($canal->video_check_next_at);
        (new VideoUploadByUserName)->handle();
        $this->assertSame($completed->subDays(2)->toRfc3339String(), $queries[2]['publishedAfter']);
    }

    public function test_name_search_budget_leaves_unattempted_channels_due_for_tomorrow(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(6, 55));
        config(['youtube.name_search.max_pages_per_run' => 1]);
        $first = Canal::factory()->create(['import_day' => 1, 'video_check_next_at' => now()]);
        $second = Canal::factory()->create(['import_day' => 1, 'video_check_next_at' => now()]);
        $this->fakeYoutube(['search' => ['items' => []]]);
        (new VideoUploadByUserName)->handle();
        $this->assertNotNull($first->fresh()->video_check_succeeded_at);
        $this->assertNull($second->fresh()->video_check_attempted_at);
        $this->travel(1)->days();
        (new VideoUploadByUserName)->handle();
        $this->assertNotNull($second->fresh()->video_check_succeeded_at);
    }

    public function test_name_search_quota_stops_run_and_does_not_advance_window(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(6, 55));
        $canal = Canal::factory()->create(['import_day' => 1]);
        $other = Canal::factory()->create(['import_day' => 1]);
        $failed = true;
        $this->fakeYoutube(['search' => function () use (&$failed) {
            return $failed ? $this->youtubeError(403, 'quotaExceeded') : ['items' => []];
        }]);
        (new VideoUploadByUserName)->handle();
        $this->assertCount(1, $this->youtubeRequests('search'));
        $this->assertNull($canal->fresh()->name_search_completed_until);
        $this->assertNull($other->fresh()->video_check_attempted_at);
        $failed = false;
        $this->travel(1)->days();
        (new VideoUploadByUserName)->handle();
        $this->assertNotNull($canal->fresh()->video_check_succeeded_at);
    }

    public function test_failed_video_import_retries_same_window_and_does_not_duplicate_posts(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(6, 55));
        $canal = Canal::factory()->create(['import_day' => 1]);
        $failed = true;
        $this->fakeYoutube([
            'search' => ['items' => [['id' => ['videoId' => 'video000001']]]],
            'videos' => function () use (&$failed) {
                return $failed ? $this->youtubeError(403, 'quotaExceeded')
                    : ['items' => [$this->videoResource('video000001')]];
            },
        ]);
        (new VideoUploadByUserName)->handle();
        $this->assertNull($canal->fresh()->video_check_succeeded_at);
        $windowEnd = $canal->fresh()->name_search_window_end;
        $this->assertSame(0, $canal->posts()->count());
        $failed = false;
        $this->travel(1)->days();
        (new VideoUploadByUserName)->handle();
        $this->assertSame(1, $canal->posts()->count());
        $this->assertTrue($canal->fresh()->name_search_completed_until->eq($windowEnd));
        $this->travelTo($canal->fresh()->video_check_next_at);
        (new VideoUploadByUserName)->handle();
        $this->assertSame(1, $canal->posts()->count());
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
