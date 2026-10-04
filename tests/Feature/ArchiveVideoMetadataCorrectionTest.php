<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveVideoMetadataCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_metadata_correction_keeps_both_sources_and_preserves_editorial_titles(): void
    {
        $title = '20.9.2026 - Laci Mižík - Božia výzbroj';
        $stale = Post::factory()->create(['video_id' => 'bK0bgT-DIds', 'title' => $title]);
        $other = Post::factory()->create(['video_id' => 'TZtc0Ea0iqc', 'title' => $title]);
        $migration = require database_path('migrations/2026_10_04_160000_correct_hermanovce_video_metadata.php');
        $migration->up();
        $migration->up();
        $this->assertSame('27.9.2026 - Marek Jurčo - Cirkev ako dar', $stale->fresh()->title);
        $this->assertSame('1:26:26', $stale->fresh()->video_duration);
        $this->assertSame('2026-09-27 23:34:07', $stale->fresh()->youtube_published_at->format('Y-m-d H:i:s'));
        $this->assertSame($title, $other->fresh()->title);
        $this->assertDatabaseCount('posts', 2);
        $stale->update(['title' => 'Ručne upravený názov']);
        $migration->up();
        $this->assertSame('Ručne upravený názov', $stale->fresh()->title);
    }
}
