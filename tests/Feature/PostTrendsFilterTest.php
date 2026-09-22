<?php

namespace Tests\Feature;

use App\Filters\PostFilters;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PostTrendsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_trend_radi_najsledovanejsie_cerstve_prispevky_bez_agregacie_views(): void
    {
        $menej = Post::factory()->create([
            'published_at' => now()->subDays(2),
            'count_view' => 10,
        ]);
        $viac = Post::factory()->create([
            'published_at' => now()->subDays(7),
            'count_view' => 50,
        ]);
        Post::factory()->create([
            'published_at' => now()->subDays(15),
            'count_view' => 5000,
        ]);

        $filters = new PostFilters(Request::create('/', 'GET', ['trends' => 'true']));
        $query = Post::query()->filter($filters);

        $this->assertStringNotContainsString('views', strtolower($query->toSql()));
        $this->assertSame([$viac->id, $menej->id], $query->pluck('id')->all());
    }
}
