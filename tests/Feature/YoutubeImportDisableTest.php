<?php

namespace Tests\Feature;

use Alaouy\Youtube\Facades\Youtube;
use App\Models\Canal;
use App\Models\User;
use App\Notifications\Admin\YoutubeImportIssue;
use App\Notifications\Admin\YoutubeSourceMissing;
use App\Repositories\Eloquent\EloquentCanalRepository;
use App\Services\VideoUpload;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Kanál, ktorý na YouTube už neexistuje, sa má z denného importu vyradiť
 * a superadmin o tom má vedieť. YouTube je namockovaný — do API sa nevolá.
 */
class YoutubeImportDisableTest extends TestCase
{
    use RefreshDatabase;

    /** Neexistujúci kanál: YouTube odpovie na activities.list chybou 403. */
    private const FORBIDDEN = 'Error 403 The request is not properly authorized. : forbidden';

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

    public function testZmazanyKanalSaVypneAPrideOznamSuperadminovi()
    {
        $superadmin = $this->superadmin();
        $canal = $this->canal(['youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw']);

        Youtube::shouldReceive('getActivitiesByChannelId')
            ->once()
            ->andThrow(new \Exception(self::FORBIDDEN));

        // channels.list potvrdí, že kanál naozaj neexistuje.
        Youtube::shouldReceive('getChannelById')->once()->andReturn(false);

        Notification::fake();

        (new VideoUpload)->handle();

        $canal->refresh();

        $this->assertNotNull($canal->youtube_disabled_at);
        $this->assertStringContainsString('neexistuje', $canal->youtube_disabled_reason);

        Notification::assertSentTo($superadmin, YoutubeSourceMissing::class);
    }

    public function testVypnutyKanalSaDoImportuNedostane()
    {
        $this->canal([
            'youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw',
            'youtube_disabled_at' => now(),
            'youtube_disabled_reason' => 'kanál na YouTube už neexistuje',
        ]);

        // Ani jeden dopyt na YouTube — kanál je z importu vyradený.
        Youtube::shouldReceive('getActivitiesByChannelId')->never();

        (new VideoUpload)->handle();

        $this->assertCount(0, (new EloquentCanalRepository)->getYoutubeVideos());
    }

    public function testVycerpanaKvotaKanalNevypne()
    {
        $superadmin = $this->superadmin();
        $canal = $this->canal(['youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw']);

        Youtube::shouldReceive('getActivitiesByChannelId')
            ->once()
            ->andThrow(new \Exception('Error 403 The request cannot be completed because you have exceeded your quota. : quotaExceeded'));

        // Overenie kanála zlyhá z toho istého dôvodu, takže o zmazaní kanála
        // nič nevieme a vypínať ho nesmieme.
        Youtube::shouldReceive('getChannelById')
            ->once()
            ->andThrow(new \Exception('Error 403 quotaExceeded'));

        Notification::fake();

        (new VideoUpload)->handle();

        $this->assertNull($canal->refresh()->youtube_disabled_at);

        Notification::assertNotSentTo($superadmin, YoutubeSourceMissing::class);
    }

    public function testZmazanyPlaylistPriFunkcnomKanaliNevypneImport()
    {
        $canal = $this->canal([
            'youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw',
            'youtube_playlist' => 'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU',
        ]);

        Youtube::shouldReceive('getActivitiesByChannelId')->once()->andReturn([]);

        Youtube::shouldReceive('getPlaylistItemsByPlaylistId')
            ->once()
            ->andThrow(new \Exception('Error 404 The playlist cannot be found. : playlistNotFound'));

        (new VideoUpload)->handle();

        $this->assertNull($canal->refresh()->youtube_disabled_at);
    }

    public function testAdresaKanalaSaPrepiseNaIdAImportPokracuje()
    {
        $canal = $this->canal(['youtube_channel' => 'https://www.youtube.com/@EVSchcemviac']);

        Youtube::shouldReceive('getChannelByHandle')
            ->once()
            ->with('@EVSchcemviac', [], ['id'])
            ->andReturn((object) ['id' => 'UCtWheHmWwuokUASxXus4NBw']);

        Youtube::shouldReceive('getActivitiesByChannelId')
            ->once()
            ->with('UCtWheHmWwuokUASxXus4NBw')
            ->andReturn([]);

        (new VideoUpload)->handle();

        $this->assertSame('UCtWheHmWwuokUASxXus4NBw', $canal->refresh()->youtube_channel);
        $this->assertNull($canal->youtube_disabled_at);
    }

    public function testKanalBezZdrojaSaNevypina()
    {
        $canal = $this->canal(['youtube_channel' => null, 'youtube_playlist' => null]);

        Youtube::shouldReceive('getActivitiesByChannelId')->never();

        (new VideoUpload)->handle();

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

        Youtube::shouldReceive('getChannelByHandle')
            ->once()
            ->andReturn((object) ['id' => 'UCtWheHmWwuokUASxXus4NBw']);

        Youtube::shouldReceive('getActivitiesByChannelId')->once()->andReturn([]);

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
            'youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw',
            'youtube_playlist' => 'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU',
        ]);

        Youtube::shouldReceive('getActivitiesByChannelId')->once()->andReturn([]);

        Youtube::shouldReceive('getPlaylistItemsByPlaylistId')
            ->once()
            ->andThrow(new \Exception('Error 404 The playlist cannot be found. : playlistNotFound'));

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
            'youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw',
            'youtube_playlist' => 'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU',
        ]);

        Youtube::shouldReceive('getActivitiesByChannelId')->twice()->andReturn([]);

        Youtube::shouldReceive('getPlaylistItemsByPlaylistId')
            ->twice()
            ->andThrow(new \Exception('Error 404 The playlist cannot be found. : playlistNotFound'));

        (new VideoUpload)->handle();
        (new VideoUpload)->handle();

        $this->assertSame(1, $superadmin->notifications()->count());
    }
}
