<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Post;
use App\Services\Images\StoreImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageJpgVariantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['images.disk' => 'public', 'images.remote_base' => null]);
        Storage::fake('public');
    }

    public function test_upload_stores_jpg_only_in_largest_width_and_thumb_points_to_smallest_webp(): void
    {
        $post = Post::factory()->create();

        $image = StoreImage::for($post)->fromUpload(UploadedFile::fake()->image('foto.jpg', 1600, 900));

        $this->assertSame([1200], array_keys($image->variants['jpg']));
        $this->assertSame([1200, 800, 400], array_keys($image->variants['webp']));
        $this->assertSame($image->variants['jpg'][1200], $image->url);
        $this->assertSame($image->variants['webp'][400], $image->thumb);

        foreach ([...$image->variants['jpg'], ...$image->variants['webp']] as $path) {
            Storage::disk('public')->assertExists($path);
        }

        $this->assertCount(4, Storage::disk('public')->allFiles());
    }

    public function test_prune_removes_smaller_jpg_and_switches_thumb_to_webp(): void
    {
        $image = $this->legacyImage();
        $disk = Storage::disk('public');

        $this->artisan('images:prune-jpg')->assertSuccessful();

        $image->refresh();
        $this->assertSame([1200 => 'posts/1/a-w1200.jpg'], $image->variants['jpg']);
        $this->assertCount(3, $image->variants['webp']);
        $this->assertSame('posts/1/a-w400.webp', $image->thumb);
        $this->assertSame('posts/1/a-w1200.jpg', $image->url);

        $disk->assertExists('posts/1/a-w1200.jpg');
        $disk->assertMissing(['posts/1/a-w800.jpg', 'posts/1/a-w400.jpg']);
        $disk->assertExists(['posts/1/a-w1200.webp', 'posts/1/a-w800.webp', 'posts/1/a-w400.webp']);

        // Druhý beh nemá čo robiť.
        $this->artisan('images:prune-jpg')->expectsOutputToContain('upravené záznamy: 0')->assertSuccessful();
    }

    public function test_dry_run_changes_nothing(): void
    {
        $image = $this->legacyImage();

        $this->artisan('images:prune-jpg', ['--dry-run' => true])
            ->expectsOutputToContain('upravené záznamy: 1')
            ->assertSuccessful();

        $this->assertCount(3, $image->fresh()->variants['jpg']);
        $this->assertSame('posts/1/a-w400.jpg', $image->fresh()->thumb);
        Storage::disk('public')->assertExists(['posts/1/a-w800.jpg', 'posts/1/a-w400.jpg']);
    }

    public function test_image_without_webp_counterpart_is_skipped(): void
    {
        $image = $this->legacyImage(withWebp400: false);

        $this->artisan('images:prune-jpg')->expectsOutputToContain('preskočené:       1')->assertSuccessful();

        $this->assertCount(3, $image->fresh()->variants['jpg']);
        Storage::disk('public')->assertExists('posts/1/a-w400.jpg');
    }

    /** Záznam v tvare, aký StoreImage ukladal pred zmenou: JPG aj WebP v každej šírke. */
    private function legacyImage(bool $withWebp400 = true): Image
    {
        $variants = ['jpg' => [], 'webp' => []];

        foreach ([1200, 800, 400] as $width) {
            foreach (['jpg', 'webp'] as $ext) {
                if ($ext === 'webp' && $width === 400 && ! $withWebp400) {
                    continue;
                }

                $path = "posts/1/a-w{$width}.{$ext}";
                Storage::disk('public')->put($path, str_repeat('x', $width));
                $variants[$ext][$width] = $path;
            }
        }

        return Image::factory()->create([
            'url' => 'posts/1/a-w1200.jpg',
            'thumb' => 'posts/1/a-w400.jpg',
            'variants' => $variants,
        ]);
    }
}
