<?php

namespace Tests\Feature;

use App\Enums\PostSection;
use App\Models\Canal;
use App\Models\Post;
use App\Services\VideoUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakesYoutube;
use Tests\TestCase;

/**
 * Denný import videí z kanála: uploads playlist so stránkovaním, detaily
 * videí po dávkach a uloženie údajov, ktoré sa predtým zahadzovali.
 */
class YoutubeVideoImportTest extends TestCase
{
    use RefreshDatabase, FakesYoutube;

    private const CHANNEL = 'UCtWheHmWwuokUASxXus4NBw';

    private const UPLOADS = 'UUtWheHmWwuokUASxXus4NBw';

    private function canal(array $attributes = []): Canal
    {
        return Canal::factory()->create($attributes + ['youtube_channel' => self::CHANNEL]);
    }

    private function videos(string ...$ids): array
    {
        return collect($ids)->mapWithKeys(fn ($id) => [$id => $this->videoResource($id)])->all();
    }

    public function test_prvy_import_kanala_stiahne_len_najnovsie_videa(): void
    {
        $this->canal();

        $this->fakeYoutube([
            'playlistItems' => $this->playlistPage(['video000001', 'video000002'], 'p2'),
            'videos' => $this->videosFrom($this->videos('video000001', 'video000002')),
        ]);

        $this->assertSame(2, (new VideoUpload)->handle());

        // Jedna stránka s menším počtom položiek, bez listovania ďalej.
        $this->assertCount(1, $this->youtubeRequests('playlistItems'));
        $this->assertStringContainsString('maxResults=20', $this->youtubeRequests('playlistItems')->first()->url());
    }

    public function test_kanal_so_zastavenym_importom_prejde_viac_stranok_a_detaily_vypyta_jednym_dopytom(): void
    {
        $canal = $this->canal();
        // Staré video mimo playlistu — kanál už na webe niečo má.
        Post::factory()->for($canal, 'canal')->create(['video_id' => 'oldvideo001']);

        $this->fakeYoutube([
            'playlistItems' => fn ($query) => ($query['pageToken'] ?? null) === 'p2'
                ? $this->playlistPage(['video000003'])
                : $this->playlistPage(['video000001', 'video000002'], 'p2'),
            'videos' => $this->videosFrom($this->videos('video000001', 'video000002', 'video000003')),
        ]);

        $saved = (new VideoUpload)->handle();

        $this->assertSame(3, $saved);
        $this->assertCount(2, $this->youtubeRequests('playlistItems'));
        $this->assertCount(1, $this->youtubeRequests('videos'));

        $post = Post::where('video_id', 'video000001')->firstOrFail();

        $this->assertSame($canal->id, $post->canal_id);
        $this->assertSame('PT12M3S', $post->getRawOriginal('video_duration'));
        $this->assertTrue($post->youtube_published_at->utc()->eq('2026-09-10 08:00:00'));
        // Bežný kanál ide cez buffer.
        $this->assertNull($post->published_at);
    }

    public function test_stranka_so_znamym_videom_je_posledna(): void
    {
        $canal = $this->canal();
        Post::factory()->for($canal, 'canal')->create(['video_id' => 'video000002']);

        $this->fakeYoutube([
            'playlistItems' => $this->playlistPage(['video000001', 'video000002'], 'p2'),
            'videos' => $this->videosFrom($this->videos('video000001')),
        ]);

        $this->assertSame(1, (new VideoUpload)->handle());
        $this->assertCount(1, $this->youtubeRequests('playlistItems'));
        $this->assertStringContainsString('id=video000001&', $this->youtubeRequests('videos')->first()->url());
    }

    /** Predtým playlist prebil kanál a videá z kanála sa zahodili. */
    public function test_videa_z_kanala_aj_playlistu_sa_zluci(): void
    {
        $this->canal(['youtube_playlist' => 'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU']);

        $this->fakeYoutube([
            'playlistItems' => fn ($query) => $query['playlistId'] === self::UPLOADS
                ? $this->playlistPage(['video000001'])
                : $this->playlistPage(['video000002']),
            'videos' => $this->videosFrom($this->videos('video000001', 'video000002')),
        ]);

        $this->assertSame(2, (new VideoUpload)->handle());
        $this->assertSame(2, Post::whereIn('video_id', ['video000001', 'video000002'])->count());
    }

    public function test_zmazane_nevlozitelne_a_ohlasene_video_sa_preskoci(): void
    {
        $this->canal();

        $this->fakeYoutube([
            'playlistItems' => $this->playlistPage(['video000001', 'video000002', 'video000003', 'video000004']),
            'videos' => $this->videosFrom([
                'video000001' => $this->videoResource('video000001', ['status' => ['embeddable' => false]]),
                'video000002' => $this->videoResource('video000002', ['snippet' => ['liveBroadcastContent' => 'upcoming']]),
                // video000003 YouTube nevráti — je zmazané alebo súkromné.
                'video000004' => $this->videoResource('video000004'),
            ]),
        ]);

        $this->assertSame(1, (new VideoUpload)->handle());
        $this->assertSame(['video000004'], Post::pluck('video_id')->all());
    }

    public function test_kanal_s_prenosmi_zverejni_hned_aj_ohlaseny_prenos(): void
    {
        $this->canal(['post_section' => 'live']);

        $this->fakeYoutube([
            'playlistItems' => $this->playlistPage(['video000001']),
            'videos' => $this->videosFrom([
                'video000001' => $this->videoResource('video000001', [
                    'snippet' => ['liveBroadcastContent' => 'upcoming'],
                    'contentDetails' => ['duration' => 'P0D'],
                ]),
            ]),
        ]);

        (new VideoUpload)->handle();

        $post = Post::where('video_id', 'video000001')->firstOrFail();

        $this->assertSame(PostSection::Live, $post->section);
        $this->assertNotNull($post->published_at);
        $this->assertNull($post->getRawOriginal('video_duration'));
    }

    /** Staré videá kanála s dlho stojacím importom idú v bufferi do archívu. */
    public function test_stare_video_dostane_datum_z_youtube_aby_islo_do_archivu(): void
    {
        $this->canal();

        $this->fakeYoutube([
            'playlistItems' => $this->playlistPage(['video000001', 'video000002']),
            'videos' => $this->videosFrom([
                'video000001' => $this->videoResource('video000001', ['snippet' => ['publishedAt' => '2024-03-01T10:00:00Z']]),
                'video000002' => $this->videoResource('video000002', ['snippet' => ['publishedAt' => now()->subDay()->toIso8601String()]]),
            ]),
        ]);

        (new VideoUpload)->handle();

        $old = Post::where('video_id', 'video000001')->firstOrFail();
        $fresh = Post::where('video_id', 'video000002')->firstOrFail();

        $this->assertTrue($old->created_at->eq($old->youtube_published_at));
        $this->assertTrue($fresh->created_at->isToday());
    }

    /** Filter mal prehodené argumenty a kanál 256 neprepustil nič. */
    public function test_filter_kanala_256_prepusti_len_bohosluzby(): void
    {
        $this->canal(['id' => 256]);

        $this->fakeYoutube([
            'playlistItems' => $this->playlistPage(['video000001', 'video000002']),
            'videos' => $this->videosFrom([
                'video000001' => $this->videoResource('video000001', ['snippet' => ['title' => 'Bohoslužba Banská Bystrica 7. 9. 2026']]),
                'video000002' => $this->videoResource('video000002', ['snippet' => ['title' => 'Mládežnícke stretnutie']]),
            ]),
        ]);

        (new VideoUpload)->handle();

        $this->assertSame(['video000001'], Post::pluck('video_id')->all());
    }

    public function test_prikaz_spracuje_len_zvoleny_kanal(): void
    {
        $zvoleny = $this->canal();
        $this->canal(['youtube_channel' => 'UCznO9E4iMXuDyTbJr5e26tg']);

        $this->fakeYoutube([
            'playlistItems' => $this->playlistPage([]),
        ]);

        $this->artisan('UserSearchByChannelAndPlaylist', ['--canal' => $zvoleny->id])->assertSuccessful();

        $this->assertCount(1, $this->youtubeRequests('playlistItems'));
        $this->assertStringContainsString('playlistId=' . self::UPLOADS, $this->youtubeRequests('playlistItems')->first()->url());
    }
}
