<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Seminar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HanusSeminarPlaylistsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_playlists_fill_missing_years_and_correct_life_2019_without_overwriting_custom_sources(): void
    {
        $canal = Canal::factory()->create();
        $create = fn ($year, $playlist = null) => Seminar::create([
            'canal_id' => $canal->id, 'title' => "Bratislavské Hanusove dni {$year}", 'youtube_playlist' => $playlist,
        ]);
        $missing = $create(2023);
        $life = $create(2019, 'PLCu4owYx2YST29lvJlfZF7hg4Lga1Fdk2');
        $custom = $create(2024, 'PLcustom_manually_selected_playlist');
        $future = $create(2026);
        $deleted = $create(2025);
        $deleted->delete();
        $migration = require database_path('migrations/2026_10_04_180000_set_hanus_seminar_playlists.php');
        $migration->up();
        $migration->up();
        $this->assertSame('PLCu4owYx2YSTV5CBymgvp9Vg5vPRHrAFj', $missing->fresh()->youtube_playlist);
        $this->assertSame('PLCu4owYx2YSSZBqLbKWuvqbzpRAOk-xAt', $life->fresh()->youtube_playlist);
        $this->assertSame('PLcustom_manually_selected_playlist', $custom->fresh()->youtube_playlist);
        $this->assertNull($future->fresh()->youtube_playlist);
        $this->assertNull(Seminar::withTrashed()->find($deleted->id)->youtube_playlist);
        $this->assertSame($canal->id, $missing->fresh()->canal_id);
    }
}
