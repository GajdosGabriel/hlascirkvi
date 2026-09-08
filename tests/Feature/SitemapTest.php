<?php

namespace Tests\Feature;

use App\Http\Controllers\Public\SitemapController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    public function test_posts_without_slugs_are_excluded_before_pagination(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'seo.url' => 'https://hlascirkvi.sk',
        ]);
        DB::purge('sqlite');

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->boolean('published');
            $table->softDeletes();
        });
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('slug')->nullable();
            $table->boolean('youtube_blocked')->default(false);
            $table->boolean('video_available')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('organizations')->insert(['id' => 1, 'published' => 1]);
        foreach ([null, '', 'valid-slug', '0'] as $id => $slug) {
            DB::table('posts')->insert([
                'id' => $id + 1,
                'organization_id' => 1,
                'slug' => $slug,
            ]);
        }

        $controller = new class extends SitemapController
        {
            protected const PER_FILE = 1;
        };

        $first = $controller->posts(1);
        $this->assertSame(200, $first->getStatusCode());
        $this->assertStringContainsString('/post/3/valid-slug</loc>', $first->getContent());
        $this->assertStringContainsString('/post/4/0</loc>', $controller->posts(2)->getContent());

        $index = $controller->index()->getContent();
        $this->assertStringContainsString('/sitemap-prispevky-2.xml</loc>', $index);
        $this->assertStringNotContainsString('/sitemap-prispevky-3.xml</loc>', $index);
    }
}
