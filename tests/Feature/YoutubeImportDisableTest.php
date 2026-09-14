<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\User;
use App\Notifications\Admin\YoutubeImportIssue;
use App\Notifications\Admin\YoutubeSourceMissing;
use App\Repositories\Eloquent\EloquentCanalRepository;
use App\Services\VideoUpload;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\Support\FakesYoutube;
use Tests\TestCase;

/**
 * Kanál, ktorý na YouTube už neexistuje, sa má z denného importu vyradiť
 * a superadmin o tom má vedieť. YouTube je falošné — do API sa nevolá.
 */
class YoutubeImportDisableTest extends TestCase
{
    use RefreshDatabase, FakesYoutube;

    private const CHANNEL = 'UCtWheHmWwuokUASxXus4NBw';

    private const UPLOADS = 'UUtWheHmWwuokUASxXus4NBw';

    private const PLAYLIST = 'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU';

    protected function setUp(): void
    {
        parent::setUp();

        // UserObserver::created volá assignRole('user').
        $this->seed(RolesSeeder::class);
    }

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user;
    }

    private function canal(array $attributes): Canal
    {
        return Canal::factory()->create($attributes);
    }

    /** Uploads playlist kanála je prázdny, playlist je zmazaný. */
    private function fakeWorkingChannelWithDeletedPlaylist(): void
    {
        $this->fakeYoutube([
            'playlistItems' => fn ($query) => $query['playlistId'] === self::PLAYLIST
                ? $this->youtubeError(404, 'playlistNotFound')
                : $this->playlistPage([]),
        ]);
    }

    public function testZmazanyKanalSaVypneAPrideOznamSuperadminovi()
    {
        $superadmin = $this->superadmin();
        $canal = $this->canal(['youtube_channel' => self::CHANNEL]);

        $this->fakeYoutube([
            'playlistItems' => $this->youtubeError(404, 'playlistNotFound'),
            // channels.list potvrdí, že kanál naozaj neexistuje.
            'channels' => ['items' => []],
        ]);

        Notification::fake();

        (new VideoUpload)->handle();

        $canal->refresh();

        $this->assertNotNull($canal->youtube_disabled_at);
        $this->assertStringContainsString('neexistuje', $canal->youtube_disabled_reason);

        Notification::assertSentTo($superadmin, YoutubeSourceMissing::class);
    }

    public function testKanalBezVideiNieJeZmazany()
    {
        $canal = $this->canal(['youtube_channel' => self::CHANNEL]);

        // Kanál bez jediného videa nemá uploads playlist, ale existuje.
        $this->fakeYoutube([
            'playlistItems' => $this->youtubeError(404, 'playlistNotFound'),
            'channels' => ['items' => [['id' => self::CHANNEL]]],
        ]);

        (new VideoUpload)->handle();

        $this->assertNull($canal->refresh()->youtube_disabled_at);
    }

    public function testVypnutyKanalSaDoImportuNedostane()
    {
        $this->canal([
            'youtube_channel' => self::CHANNEL,
            'youtube_disabled_at' => now(),
            'youtube_disabled_reason' => 'kanál na YouTube už neexistuje',
        ]);

        // Ani jeden dopyt na YouTube — kanál je z importu vyradený.
        $this->fakeYoutube([]);

        (new VideoUpload)->handle();

        Http::assertNothingSent();
        $this->assertCount(0, (new EloquentCanalRepository)->getYoutubeVideos());
    }

    public function testVycerpanaKvotaKanalNevypne()
    {
        $superadmin = $this->superadmin();
        $canal = $this->canal(['youtube_channel' => self::CHANNEL]);
        $druhy = $this->canal(['youtube_channel' => 'UCznO9E4iMXuDyTbJr5e26tg']);

        $this->fakeYoutube([
            'playlistItems' => $this->youtubeError(403, 'quotaExceeded'),
        ]);

        Notification::fake();

        (new VideoUpload)->handle();

        $this->assertNull($canal->refresh()->youtube_disabled_at);
        $this->assertNull($druhy->refresh()->youtube_disabled_at);

        Notification::assertNotSentTo($superadmin, YoutubeSourceMissing::class);

        // O zmazaní kanála pri vyčerpanej kvóte nič nevieme a zvyšné kanály
        // by zlyhali rovnako — beh končí po prvom dopyte.
        $this->assertCount(0, $this->youtubeRequests('channels'));
        $this->assertCount(1, $this->youtubeRequests('playlistItems'));
    }

    public function testZmazanyPlaylistPriFunkcnomKanaliNevypneImport()
    {
        $canal = $this->canal([
            'youtube_channel' => self::CHANNEL,
            'youtube_playlist' => self::PLAYLIST,
        ]);

        $this->fakeWorkingChannelWithDeletedPlaylist();

        (new VideoUpload)->handle();

        $this->assertNull($canal->refresh()->youtube_disabled_at);
    }

    public function testAdresaKanalaSaPrepiseNaIdAImportPokracuje()
    {
        $canal = $this->canal(['youtube_channel' => 'https://www.youtube.com/@EVSchcemviac']);

        $this->fakeYoutube([
            'channels' => fn ($query) => ($query['forHandle'] ?? null) === '@EVSchcemviac'
                ? ['items' => [['id' => self::CHANNEL]]]
                : ['items' => []],
            'playlistItems' => $this->playlistPage([]),
        ]);

        (new VideoUpload)->handle();

        $this->assertSame(self::CHANNEL, $canal->refresh()->youtube_channel);
        $this->assertNull($canal->youtube_disabled_at);

        $this->assertStringContainsString('playlistId=' . self::UPLOADS, $this->youtubeRequests('playlistItems')->first()->url());
    }

    public function testKanalBezZdrojaSaNevypina()
    {
        $canal = $this->canal(['youtube_channel' => null, 'youtube_playlist' => null]);

        $this->fakeYoutube([]);

        (new VideoUpload)->handle();

        Http::assertNothingSent();
        $this->assertNull($canal->refresh()->youtube_disabled_at);
    }

    /**
     * Prepis adresy kanála na ID sa dial ticho v logu. Správca sa má
     * dozvedieť, že sa mu údaj v kanáli zmenil, a vedieť ho skontrolovať.
     */
    public function testPrepisanaAdresaKanalaPrideSuperadminoviDoNotifikacie()
    {
        $superadmin = $this->superadmin();
        $this->canal(['youtube_channel' => 'https://www.youtube.com/@EVSchcemviac']);

        $this->fakeYoutube([
            'channels' => ['items' => [['id' => self::CHANNEL]]],
            'playlistItems' => $this->playlistPage([]),
        ]);

        Notification::fake();

        (new VideoUpload)->handle();

        Notification::assertSentTo(
            $superadmin,
            YoutubeImportIssue::class,
            fn ($notification) => str_contains($notification->toArray($superadmin)['message'], 'prepísaná na ID')
        );
    }

    /**
     * Kanál beží ďalej, takže import nevypíname — ale zlý playlist je preklep
     * vo formulári a nikto ho neopraví, kým o ňom nevie.
     */
    public function testZmazanyPlaylistPriFunkcnomKanaliPrideDoNotifikacie()
    {
        $superadmin = $this->superadmin();
        $this->canal([
            'youtube_channel' => self::CHANNEL,
            'youtube_playlist' => self::PLAYLIST,
        ]);

        $this->fakeWorkingChannelWithDeletedPlaylist();

        Notification::fake();

        (new VideoUpload)->handle();

        Notification::assertSentTo(
            $superadmin,
            YoutubeImportIssue::class,
            fn ($notification) => str_contains($notification->toArray($superadmin)['message'], 'playlist')
        );
    }

    /** Import beží denne — neprečítané hlásenie sa nemá kopiť v zvončeku. */
    public function testNeprecitaneHlasenieSaNaDruhyDenNeopakuje()
    {
        $superadmin = $this->superadmin();
        $this->canal([
            'youtube_channel' => self::CHANNEL,
            'youtube_playlist' => self::PLAYLIST,
        ]);

        $this->fakeWorkingChannelWithDeletedPlaylist();

        (new VideoUpload)->handle();
        (new VideoUpload)->handle();

        $this->assertSame(1, $superadmin->notifications()->count());
    }
}
