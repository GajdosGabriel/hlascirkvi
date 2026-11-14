<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\Seminar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_detail_nenacitava_facebook_sdk_ani_cely_model_do_props(): void
    {
        $post = Post::factory()->create(['body' => 'Jedinečný text príspevku na kontrolu.']);

        $response = $this->get(route('post.show', [$post->id, $post->slug]))->assertOk();

        $response->assertDontSee('connect.facebook.net/sk_SK/sdk.js#xfbml', false);
        // Vue props nesú len vybrané polia, text príspevku v nich nie je.
        $response->assertDontSee('&quot;body&quot;', false);
    }

    public function test_diel_serie_ukaze_polohu_a_susedne_diely(): void
    {
        $canal = Canal::factory()->create();
        $parts = Post::factory()->count(3)->create(['canal_id' => $canal->id]);
        $seminar = Seminar::create(['title' => 'Kurz Alfa', 'published' => now(), 'canal_id' => $canal->id]);
        $seminar->posts()->attach($parts->pluck('id'));

        $middle = $parts[1];

        $this->get(route('post.show', [$middle->id, $middle->slug]))
            ->assertOk()
            ->assertSee('časť 2 z 3')
            ->assertSee('Kurz Alfa')
            ->assertSee(route('post.show', [$parts[0]->id, $parts[0]->slug]), false)
            ->assertSee(route('post.show', [$parts[2]->id, $parts[2]->slug]), false);
    }

    public function test_unavailable_video_keeps_detail_but_is_hidden_from_public_lists(): void
    {
        $post = Post::factory()->create(['video_id' => 'abcdefghijk', 'video_available' => false]);
        $this->get(route('post.show', [$post->id, $post->slug]))
            ->assertOk()
            ->assertSee('Video je momentálne nedostupné')
            ->assertDontSee('data-yt-lite="abcdefghijk"', false)
            ->assertDontSee('VideoObject', false)
            ->assertSee($post->title);

        $repository = new \App\Repositories\Eloquent\EloquentPostRepository;
        $this->assertFalse($repository->postsInSection(\App\Enums\PostSection::Front)->whereKey($post->id)->exists());
        $this->assertSame(0, $repository->countInCanal($post->canal_id));
        $this->get(route('organizations.show', [$post->canal_id]))->assertOk()->assertViewHas('posts', fn ($posts) => $posts->isEmpty());
        foreach ([null, true] as $available) {
            $post->update(['video_available' => $available]);
            $this->assertTrue($repository->postsInSection(\App\Enums\PostSection::Front)->whereKey($post->id)->exists());
            $this->assertSame(1, $repository->countInCanal($post->canal_id));
        }
    }

    public function test_video_bez_nazvu_ma_v_schema_nahradny_name(): void
    {
        $canal = Canal::factory()->create(['title' => 'Godzone']);
        // cleanTitle() emoji zahodí — z názvu ostane prázdny reťazec.
        $post = Post::factory()->create([
            'canal_id' => $canal->id,
            'title' => '🤫🤫🤫',
            'video_id' => 'abcdefghijk',
            'video_available' => true,
            'created_at' => '2026-11-05 10:28:14',
        ]);
        $this->assertSame('', $post->fresh()->title);

        $html = $this->get(route('post.show', [$post->id]))->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $schemas = collect($matches[1])->map(fn ($json) => json_decode($json, true));
        $video = $schemas->firstWhere('@type', 'VideoObject');
        $crumbs = $schemas->firstWhere('@type', 'BreadcrumbList');

        $this->assertSame('Video z kanála Godzone z 5. 11. 2026', $video['name']);
        $this->assertSame($video['name'], $video['headline']);
        $this->assertSame($video['name'], end($crumbs['itemListElement'])['name']);
        $this->assertStringContainsString('<title>Video z kanála Godzone z 5. 11. 2026', $html);
    }

    public function test_bez_serie_sa_lista_nezobrazi(): void
    {
        $post = Post::factory()->create();

        $this->get(route('post.show', [$post->id, $post->slug]))
            ->assertOk()
            ->assertDontSee('Diely série');
    }

    /**
     * Slug vzniká z očisteného titulku, takže hashtagy z YouTube importu
     * v adrese neostanú. Starý slug (s hashtagmi) presmeruje 301 na nový.
     */
    public function test_slug_vznika_z_ocisteneho_titulku_a_stary_presmeruje(): void
    {
        $post = Post::factory()->create(['title' => 'Ranná modlitba_Ivanka #ps#sr']);

        $this->assertSame('Ranná modlitba Ivanka', $post->title);
        $this->assertSame('ranna-modlitba-ivanka', $post->slug);

        $oldSlug = \Illuminate\Support\Str::slug('Ranná modlitba_Ivanka #ps#sr');
        $this->assertNotSame($post->slug, $oldSlug);

        $this->get('/post/' . $post->id . '/' . $oldSlug . '?utm=x')
            ->assertStatus(301)
            ->assertRedirect(route('post.show', [$post->id, 'ranna-modlitba-ivanka']) . '?utm=x');
    }
}
