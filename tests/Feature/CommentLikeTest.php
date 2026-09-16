<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentLikeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    protected function comment(): Comment
    {
        $post = Post::factory()->create(['canal_id' => Canal::factory()->create()->id]);

        return Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);
    }

    public function test_like_sa_prepina_a_vracia_pocet(): void
    {
        $comment = $this->comment();

        $this->actingAs(User::factory()->create());

        $this->postJson("/api/comments/{$comment->id}/like")
            ->assertOk()
            ->assertExactJson(['is_favorited' => true, 'favorites_count' => 1]);

        $this->postJson("/api/comments/{$comment->id}/like")
            ->assertOk()
            ->assertExactJson(['is_favorited' => false, 'favorites_count' => 0]);
    }

    public function test_zrusenie_likeu_nezmaze_likey_ostatnych(): void
    {
        $comment = $this->comment();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first)->postJson("/api/comments/{$comment->id}/like")->assertOk();
        $this->actingAs($second)->postJson("/api/comments/{$comment->id}/like")->assertOk();

        $this->actingAs($first)
            ->postJson("/api/comments/{$comment->id}/like")
            ->assertExactJson(['is_favorited' => false, 'favorites_count' => 1]);

        $this->assertDatabaseHas('favorites', [
            'favorited_id' => $comment->id,
            'favorited_type' => Comment::class,
            'user_id' => $second->id,
        ]);
    }

    public function test_host_nemoze_dat_like(): void
    {
        $comment = $this->comment();

        $this->postJson("/api/comments/{$comment->id}/like")->assertUnauthorized();

        $this->assertDatabaseMissing('favorites', ['favorited_id' => $comment->id]);
    }
}
