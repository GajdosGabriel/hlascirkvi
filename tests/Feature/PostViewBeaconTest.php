<?php

namespace Tests\Feature;

use App\Models\Post;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostViewBeaconTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    private function beacon(Post $post, array $headers = [])
    {
        return $this->withHeaders($headers + [
            'User-Agent' => self::BROWSER,
            'X-Requested-With' => 'XMLHttpRequest',
            'Sec-Fetch-Site' => 'same-origin',
        ])->post(route('post.view', $post));
    }

    public function test_render_detailu_zobrazenie_nezapise(): void
    {
        $post = Post::factory()->create(['count_view' => 5]);

        $this->withHeaders(['User-Agent' => self::BROWSER])
            ->get(route('post.show', [$post->id, $post->slug]))
            ->assertOk()
            ->assertSee(route('post.view', $post), false);

        $this->assertSame(0, DB::table('views')->count());
        $this->assertSame(5, (int) $post->fresh()->count_view);
    }

    public function test_beacon_zapise_zobrazenie_raz_za_den(): void
    {
        $post = Post::factory()->create(['count_view' => 5]);

        $this->beacon($post)->assertNoContent();
        $this->beacon($post)->assertNoContent();

        $this->assertSame(1, DB::table('views')->count());
        $this->assertSame(6, (int) $post->fresh()->count_view);
    }

    public function test_beacon_bez_xhr_hlavicky_sa_nezapocita(): void
    {
        $post = Post::factory()->create(['count_view' => 5]);

        $this->beacon($post, ['X-Requested-With' => ''])->assertNoContent();

        $this->assertSame(0, DB::table('views')->count());
        $this->assertSame(5, (int) $post->fresh()->count_view);
    }

    public function test_beacon_od_bota_sa_nezapocita(): void
    {
        $post = Post::factory()->create(['count_view' => 5]);

        $this->beacon($post, ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])->assertNoContent();

        $this->assertSame(0, DB::table('views')->count());
    }

    public function test_beacon_z_cudzej_stranky_sa_nezapocita(): void
    {
        $post = Post::factory()->create(['count_view' => 5]);

        $this->beacon($post, ['Sec-Fetch-Site' => 'cross-site'])->assertNoContent();

        $this->assertSame(0, DB::table('views')->count());
    }
}
