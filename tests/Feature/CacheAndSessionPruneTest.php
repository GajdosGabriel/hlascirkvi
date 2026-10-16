<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CacheAndSessionPruneTest extends TestCase
{
    use RefreshDatabase;

    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = storage_path('framework/testing/cache-prune-' . uniqid());
        config(['cache.stores.file.path' => $this->cacheDir]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->cacheDir);

        parent::tearDown();
    }

    public function test_expired_cache_files_and_rows_are_deleted_and_live_ones_kept(): void
    {
        $expired = $this->cacheFile('ab/cd/expired', time() - 60);
        $live = $this->cacheFile('ab/cd/live', time() + 600);
        $forever = $this->cacheFile('ef/01/forever', 9999999999);
        $partial = $this->cacheFile('ef/01/partial', null);

        DB::table('cache')->insert([
            ['key' => 'old', 'value' => 'i:1;', 'expiration' => time() - 60],
            ['key' => 'new', 'value' => 'i:1;', 'expiration' => time() + 60],
        ]);

        $this->artisan('cache:prune-expired')->assertSuccessful();

        $this->assertFileDoesNotExist($expired);
        $this->assertFileExists($live);
        $this->assertFileExists($forever);
        $this->assertFileExists($partial);
        $this->assertSame(['new'], DB::table('cache')->pluck('key')->all());
    }

    public function test_only_idle_guest_sessions_are_pruned(): void
    {
        config(['session.driver' => 'database']);
        $old = now()->subHours(3)->getTimestamp();

        DB::table('sessions')->insert([
            ['id' => 'guest-old', 'user_id' => null, 'payload' => '', 'last_activity' => $old],
            ['id' => 'guest-new', 'user_id' => null, 'payload' => '', 'last_activity' => now()->getTimestamp()],
            ['id' => 'user-old', 'user_id' => 12345, 'payload' => '', 'last_activity' => $old],
        ]);

        $this->artisan('sessions:prune-guests')->assertSuccessful();

        $this->assertEqualsCanonicalizing(['guest-new', 'user-old'], DB::table('sessions')->pluck('id')->all());
    }

    public function test_refetch_flags_unreplaceable_images_and_skips_them_next_time(): void
    {
        Http::fake(['i.ytimg.com/*' => Http::response('', 404)]);
        $post = Post::factory()->create(['video_id' => 'gone']);
        Image::query()->insert([
            'fileable_id' => $post->id,
            'fileable_type' => Post::class,
            'name' => 'x.jpg',
            'url' => 'posts/x.jpg',
            'thumb' => 'posts/x.jpg',
            'org_name' => 'x.jpg',
        ]);

        $this->artisan('images:refetch', ['--dry-run' => true])->assertSuccessful();
        $this->assertNull(Image::query()->value('refetch_failed_at'));

        $this->artisan('images:refetch')
            ->expectsOutputToContain('Na spracovanie: 1 obrázkov.')
            ->assertSuccessful();
        $this->assertNotNull(Image::query()->value('refetch_failed_at'));
        Http::assertSentCount(4);

        $this->artisan('images:refetch')
            ->expectsOutput('Nie je čo prepočítať.')
            ->assertSuccessful();
        Http::assertSentCount(4);

        $this->artisan('images:refetch', ['--retry' => true])
            ->expectsOutputToContain('Na spracovanie: 1 obrázkov.')
            ->assertSuccessful();
    }

    public function test_refetch_does_not_flag_image_when_youtube_is_unreachable(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));
        $post = Post::factory()->create(['video_id' => 'abc']);
        Image::query()->insert([
            'fileable_id' => $post->id,
            'fileable_type' => Post::class,
            'name' => 'y.jpg',
            'url' => 'posts/y.jpg',
            'thumb' => 'posts/y.jpg',
            'org_name' => 'y.jpg',
        ]);

        $this->artisan('images:refetch')->assertSuccessful();

        $this->assertNull(Image::query()->value('refetch_failed_at'));
    }

    private function cacheFile(string $path, ?int $expires): string
    {
        $file = $this->cacheDir . '/' . $path;
        File::ensureDirectoryExists(dirname($file));
        File::put($file, $expires === null ? 'xx' : $expires . 'i:1;');

        return $file;
    }
}
