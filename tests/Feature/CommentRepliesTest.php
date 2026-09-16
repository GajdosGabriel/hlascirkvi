<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\Comments\RepliedToComment;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommentRepliesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    protected function postWithComment(): array
    {
        $post = Post::factory()->create(['canal_id' => Canal::factory()->create()->id]);
        $author = User::factory()->create();
        $comment = Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'user_id' => $author->id,
        ]);

        return [$post, $comment, $author];
    }

    public function test_odpoved_sa_ulozi_pod_komentar_a_upovedomi_autora(): void
    {
        Notification::fake();
        [$post, $comment, $author] = $this->postWithComment();

        $this->actingAs(User::factory()->create())
            ->postJson("/api/posts/{$post->id}/comments", [
                'body' => 'Súhlasím s vami.',
                'parent_id' => $comment->id,
            ])
            ->assertSuccessful()
            ->assertJsonPath('parent_id', $comment->id);

        $this->assertDatabaseHas('comments', ['body' => 'Súhlasím s vami.', 'parent_id' => $comment->id]);
        Notification::assertSentTo($author, RepliedToComment::class);
    }

    public function test_odpoved_na_odpoved_patri_pod_hlavny_komentar(): void
    {
        [$post, $comment] = $this->postWithComment();
        $reply = Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'parent_id' => $comment->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson("/api/posts/{$post->id}/comments", [
                'body' => 'Odpoveď na odpoveď.',
                'parent_id' => $reply->id,
            ])
            ->assertSuccessful();

        $this->assertDatabaseHas('comments', ['body' => 'Odpoveď na odpoveď.', 'parent_id' => $comment->id]);
    }

    public function test_nemozno_odpovedat_na_komentar_ineho_prispevku(): void
    {
        [, $comment] = $this->postWithComment();
        $other = Post::factory()->create(['canal_id' => Canal::factory()->create()->id]);

        $this->actingAs(User::factory()->create())
            ->postJson("/api/posts/{$other->id}/comments", [
                'body' => 'Zablúdená odpoveď.',
                'parent_id' => $comment->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_vypis_vracia_odpovede_vnorene(): void
    {
        [$post, $comment] = $this->postWithComment();
        $reply = Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'parent_id' => $comment->id,
        ]);

        $this->getJson("/api/posts/{$post->id}/comments")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $comment->id)
            ->assertJsonPath('0.replies.0.id', $reply->id);
    }
}
