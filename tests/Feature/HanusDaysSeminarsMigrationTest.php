<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HanusDaysSeminarsMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return require database_path('migrations/2026_10_04_120000_create_hanus_days_seminars_2023_to_2026.php');
    }

    private function source(): int
    {
        $owner = Canal::factory()->create();
        DB::table('seminars')->insert([
            'title' => 'Bratislavské Hanusove dni 2022',
            'canal_id' => $owner->id, 'published' => now(),
        ]);

        return $owner->id;
    }

    public function test_groups_explicit_years_across_channels_without_duplicates_or_changing_publication(): void
    {
        $ownerId = $this->source();
        $titles = [
            2023 => 'Prednáška | BHD 2023',
            2024 => 'Diskusia | BHD24',
            2025 => 'Lecture | Bratislava Hanus Days 2025 (ENG)',
            2026 => 'Diskusia | Bratislavské Hanusove dni 2026',
        ];
        $posts = [];
        foreach ($titles as $year => $title) {
            $posts[$year] = Post::factory()->create(['title' => $title, 'section' => 'front', 'video_id' => 'bhd-video-'.$year]);
        }
        $draft = Post::factory()->unpublished()->create(['title' => 'BHD26 | Nezverejnené', 'section' => 'front', 'video_id' => 'bhd-draft']);
        $unrelated = Post::factory()->create([
            'video_id' => 'other-video', 'title' => 'Iné podujatie 2023', 'body' => 'Pozri aj BHD 2023', 'section' => 'front',
        ]);
        $ambiguous = Post::factory()->create(['title' => 'BHD23 a BHD24', 'section' => 'front', 'video_id' => 'ambiguous-video']);
        $deleted = Post::factory()->create(['title' => 'BHD 2024', 'section' => 'front', 'video_id' => 'deleted-video']);
        $deleted->delete();
        $article = Post::factory()->create(['title' => 'BHD 2025', 'video_id' => null, 'section' => 'front']);
        $oldId = DB::table('seminars')->where('title', 'Bratislavské Hanusove dni 2022')->value('id');
        DB::table('post_seminar')->insert(['seminar_id' => $oldId, 'post_id' => $posts[2023]->id]);
        $existingId = DB::table('seminars')->insertGetId([
            'title' => 'Bratislavské Hanusove dni 2024', 'canal_id' => $ownerId,
            'description' => 'Ručný popis', 'published' => now(),
        ]);

        $this->migration()->up();
        $this->migration()->up();

        foreach ($posts as $year => $post) {
            $seminars = DB::table('seminars')->where('title', "Bratislavské Hanusove dni {$year}")->get();
            $this->assertCount(1, $seminars);
            $this->assertEquals($ownerId, $seminars[0]->canal_id);
            $this->assertNotNull($seminars[0]->published);
            $this->assertDatabaseHas('post_seminar', ['seminar_id' => $seminars[0]->id, 'post_id' => $post->id]);
            $this->assertDatabaseHas('posts', ['id' => $post->id, 'section' => 'seminar']);
            $this->assertEquals($post->published_at, $post->fresh()->published_at);
        }
        $this->assertDatabaseHas('seminars', ['id' => $existingId, 'description' => 'Ručný popis']);
        $this->assertDatabaseHas('post_seminar', ['seminar_id' => $oldId, 'post_id' => $posts[2023]->id]);
        $this->assertDatabaseHas('posts', ['id' => $draft->id, 'published_at' => null, 'section' => 'seminar']);
        foreach ([$unrelated, $ambiguous, $deleted, $article] as $post) {
            $this->assertDatabaseMissing('post_seminar', ['post_id' => $post->id]);
            $this->assertDatabaseHas('posts', ['id' => $post->id, 'section' => 'front']);
        }
        $this->assertSame(6, DB::table('post_seminar')->count());
    }

    public function test_creates_2023_to_2025_but_no_empty_2026(): void
    {
        $this->source();
        $this->migration()->up();
        $this->assertSame(4, DB::table('seminars')->count());
        $this->assertDatabaseMissing('seminars', ['title' => 'Bratislavské Hanusove dni 2026']);
    }

    public function test_missing_source_is_a_no_op_on_a_fresh_database(): void
    {
        $this->migration()->up();
        $this->assertDatabaseCount('seminars', 0);
    }
}
