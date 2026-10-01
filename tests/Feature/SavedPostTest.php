<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedPostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_host_si_nic_neulozi(): void
    {
        $post = Post::factory()->create();

        $this->putJson("/post/{$post->id}/ulozit")->assertUnauthorized();
        $this->get('/ulozene')->assertRedirect('/login');
        $this->assertDatabaseCount('saved_posts', 0);
    }

    public function test_prihlaseny_ulozi_a_zrusi_ulozenie(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->putJson("/post/{$post->id}/ulozit")
            ->assertOk()
            ->assertJson(['saved' => true]);
        $this->assertDatabaseHas('saved_posts', ['user_id' => $user->id, 'post_id' => $post->id]);

        $this->actingAs($user)->putJson("/post/{$post->id}/ulozit")
            ->assertOk()
            ->assertJson(['saved' => false]);
        $this->assertDatabaseCount('saved_posts', 0);
    }

    public function test_stranka_ukaze_len_vlastne_ulozene(): void
    {
        $user = User::factory()->create();
        // Cudzí príspevok vzniká prvý: vytvorenie príspevku zapíše do session
        // flash správu s jeho názvom a tá by sa ukázala na stránke.
        $foreign = Post::factory()->create(['title' => 'Cudzia uložená kázeň']);
        $mine = Post::factory()->create(['title' => 'Moja uložená kázeň']);

        $user->savedPosts()->attach($mine);
        User::factory()->create()->savedPosts()->attach($foreign);

        $this->actingAs($user)->get('/ulozene')
            ->assertOk()
            ->assertSee('Moja uložená kázeň')
            ->assertDontSee('Cudzia uložená kázeň');
    }

    public function test_nezverejneny_prispevok_sa_ulozit_neda(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['published_at' => null]);

        $this->actingAs($user)->putJson("/post/{$post->id}/ulozit")->assertNotFound();
        $this->assertDatabaseCount('saved_posts', 0);
    }

    public function test_ulozeny_prispevok_ktory_sa_skryl_sa_da_odobrat(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $user->savedPosts()->attach($post);
        $post->update(['published_at' => null]);

        $this->actingAs($user)->putJson("/post/{$post->id}/ulozit")->assertJson(['saved' => false]);
        $this->assertDatabaseCount('saved_posts', 0);
    }

    public function test_zoznam_ukazuje_pocet_a_odstranenie(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(2)->create();
        $user->savedPosts()->attach($posts->pluck('id'));

        $this->actingAs($user)->get('/ulozene')
            ->assertOk()
            ->assertSee('Uložených: 2')
            ->assertSee('Odstrániť');

        $this->actingAs($user)->delete("/post/{$posts[0]->id}/ulozit")->assertRedirect();
        $this->assertDatabaseCount('saved_posts', 1);
    }

    public function test_chyba_404_neprezradi_model_ani_pri_vypnutom_debugu(): void
    {
        config(['app.debug' => false]);

        $response = $this->actingAs(User::factory()->create())->putJson('/post/99999999/ulozit');

        $response->assertNotFound()->assertExactJson(['message' => 'Nenájdené.']);
    }

    public function test_detail_pozna_stav_ulozenia(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $user->savedPosts()->attach($post);

        $this->actingAs($user)->get(route('post.show', [$post->id, $post->slug]))
            ->assertOk()
            ->assertSee(':initial-saved="true"', false);
    }
}
