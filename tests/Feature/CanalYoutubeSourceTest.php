<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakesYoutube;
use Tests\TestCase;

/**
 * Jedno pole „YouTube kanál alebo playlist“ (App\Services\Youtube\YoutubeSource).
 */
class CanalYoutubeSourceTest extends TestCase
{
    use RefreshDatabase, FakesYoutube;

    private const CHANNEL = 'UCdZmE1-uk26ayn5sfblvtww';

    private const PLAYLIST = 'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU';

    private function admin(Canal $canal, string $role = 'admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $canal->users()->attach($user);

        return $user->fresh();
    }

    private function save(Canal $canal, string $source, array $extra = [])
    {
        return $this->put('/dashboard/canals/' . $canal->id, $extra + [
            'title' => $canal->title, 'village_id' => $canal->village_id, 'youtube_source' => $source,
        ]);
    }

    private function fakeHandle(): void
    {
        $this->fakeYoutube([
            'channels' => fn ($q) => ($q['forHandle'] ?? null) === '@zmensa' || ($q['id'] ?? null) === self::CHANNEL
                ? ['items' => [['id' => self::CHANNEL]]]
                : ['items' => []],
            'videos' => $this->videosFrom(['dQw4w9WgXcQ' => $this->videoResource('dQw4w9WgXcQ', ['snippet' => ['channelId' => self::CHANNEL]])]),
            'search' => fn () => ['items' => [['id' => ['channelId' => 'UC' . str_repeat('x', 22)]]]],
        ]);
    }

    public function test_channel_address_in_any_form_is_saved_as_channel_id(): void
    {
        $this->fakeHandle();

        foreach (['https://www.youtube.com/@zmensa/videos', '@zmensa', 'youtube.com/@zmensa',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ', self::CHANNEL] as $input) {
            $canal = Canal::factory()->create(['youtube_playlist' => self::PLAYLIST . 'x']);
            $this->actingAs($this->admin($canal));
            $this->save($canal, $input)->assertSessionHasNoErrors()->assertRedirect();
            $canal->refresh();
            $this->assertSame(self::CHANNEL, $canal->youtube_channel, $input);
            $this->assertNull($canal->youtube_playlist, $input);
            // Ten istý kanál YouTube smie mať len jeden kanál na portáli.
            $canal->delete();
        }
    }

    public function test_playlist_address_goes_to_playlist_and_clears_channel(): void
    {
        $this->fakeHandle();
        $canal = Canal::factory()->create(['youtube_channel' => self::CHANNEL]);
        $this->actingAs($this->admin($canal));

        $this->save($canal, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&list=' . self::PLAYLIST)
            ->assertSessionHasNoErrors();
        $canal->refresh();
        $this->assertNull($canal->youtube_channel);
        $this->assertSame(self::PLAYLIST, $canal->youtube_playlist);
    }

    public function test_unknown_handle_is_rejected_instead_of_guessing_by_name(): void
    {
        $this->fakeHandle();
        $canal = Canal::factory()->create();
        $this->actingAs($this->admin($canal));

        $this->save($canal, '@neexistuje')->assertSessionHasErrors('youtube_channel');
        $this->assertNull($canal->fresh()->youtube_channel);
        $this->assertCount(0, $this->youtubeRequests('search'));
    }

    public function test_empty_field_removes_source(): void
    {
        $this->fakeHandle();
        $canal = Canal::factory()->create(['youtube_channel' => self::CHANNEL]);
        $this->actingAs($this->admin($canal));

        $this->save($canal, '')->assertSessionHasNoErrors();
        $this->assertNull($canal->fresh()->youtube_channel);
    }

    public function test_new_source_gets_day_unless_admin_chose_one(): void
    {
        $this->fakeHandle();
        $canal = Canal::factory()->create(['import_day' => null]);
        $this->actingAs($this->admin($canal));
        $this->save($canal, '@zmensa', ['import_day' => 'auto'])->assertSessionHasNoErrors();
        $this->assertNotNull($canal->fresh()->import_day);
        $canal->delete();

        $canal = Canal::factory()->create(['import_day' => null]);
        $this->actingAs($this->admin($canal));
        $this->save($canal, '@zmensa', ['import_day' => 4])->assertSessionHasNoErrors();
        $this->assertSame(4, $canal->fresh()->import_day);
    }

    public function test_check_endpoint_reports_channel_playlist_and_failure(): void
    {
        $this->fakeYoutube([
            'channels' => fn ($q) => ($q['forHandle'] ?? null) === '@zmensa' || ($q['id'] ?? null) === self::CHANNEL
                ? ['items' => [['id' => self::CHANNEL]]]
                : ['items' => []],
            'playlistItems' => fn ($q) => $q['playlistId'] === self::PLAYLIST
                ? $this->playlistPage(['dQw4w9WgXcQ'])
                : $this->youtubeError(404, 'playlistNotFound'),
        ]);
        $canal = Canal::factory()->create();
        $this->actingAs($this->admin($canal));

        $this->getJson('/dashboard/youtube-source?value=' . urlencode('https://www.youtube.com/@zmensa/videos'))
            ->assertOk()->assertJson(['ok' => true, 'type' => 'channel', 'id' => self::CHANNEL]);
        $this->getJson('/dashboard/youtube-source?value=' . self::PLAYLIST)
            ->assertOk()->assertJson(['ok' => true, 'type' => 'playlist']);
        $this->getJson('/dashboard/youtube-source?value=PL' . str_repeat('z', 30))
            ->assertOk()->assertJson(['ok' => false]);
        $this->getJson('/dashboard/youtube-source?value=' . urlencode('@nikto'))
            ->assertOk()->assertJson(['ok' => false]);
    }

    public function test_check_endpoint_is_admin_only(): void
    {
        $canal = Canal::factory()->create();
        $this->actingAs($this->admin($canal, 'user'))
            ->getJson('/dashboard/youtube-source?value=@zmensa')->assertForbidden();
    }

    public function test_form_shows_single_source_field(): void
    {
        $canal = Canal::factory()->create(['youtube_channel' => self::CHANNEL]);
        $this->actingAs($this->admin($canal));
        $this->get('/dashboard/canals/' . $canal->id . '/edit')->assertOk()
            ->assertSee('name="youtube_source"', false)
            ->assertSee('value="' . self::CHANNEL . '"', false)
            ->assertDontSee('name="youtube_playlist"', false);
    }
}
