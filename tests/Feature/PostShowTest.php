<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\Seminar;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
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
        $seminar = Seminar::create(['title' => 'Kurz Alfa', 'canal_id' => $canal->id]);
        $seminar->posts()->attach($parts->pluck('id'));

        $middle = $parts[1];

        $this->get(route('post.show', [$middle->id, $middle->slug]))
            ->assertOk()
            ->assertSee('časť 2 z 3')
            ->assertSee('Kurz Alfa')
            ->assertSee(route('post.show', [$parts[0]->id, $parts[0]->slug]), false)
            ->assertSee(route('post.show', [$parts[2]->id, $parts[2]->slug]), false);
    }

    public function test_bez_serie_sa_lista_nezobrazi(): void
    {
        $post = Post::factory()->create();

        $this->get(route('post.show', [$post->id, $post->slug]))
            ->assertOk()
            ->assertDontSee('Diely série');
    }
}
