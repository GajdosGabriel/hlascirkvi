<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Prayer;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentPrayerPublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_new_records_publish_now_and_explicit_null_stays_hidden(): void
    {
        $this->freezeTime();
        foreach ([Comment::class, Prayer::class] as $model) {
            $record = $model::factory()->create(['created_at' => now()->subWeek()]);
            $this->assertSame(now()->toDateTimeString(), $record->fresh()->published->toDateTimeString());
            $this->assertNull($model::factory()->create(['published' => null])->fresh()->published);
        }
    }

    public function test_public_comment_lists_exclude_hidden_comments_and_replies(): void
    {
        $post = Post::factory()->create();
        $root = Comment::factory()->create(['commentable_id' => $post->id]);
        $hidden = Comment::factory()->create(['commentable_id' => $post->id, 'published' => null]);
        Comment::factory()->create(['commentable_id' => $post->id, 'parent_id' => $root->id, 'published' => null]);
        Comment::factory()->create(['commentable_id' => $post->id, 'parent_id' => $hidden->id]);
        $reply = Comment::factory()->create(['commentable_id' => $post->id, 'parent_id' => $root->id]);

        $this->getJson("/api/posts/{$post->id}/comments")->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $root->id)
            ->assertJsonCount(1, '0.replies')
            ->assertJsonPath('0.replies.0.id', $reply->id);
        $this->getJson('/api/comments')->assertOk()->assertJsonCount(2);
    }

    public function test_hidden_comments_cannot_receive_replies(): void
    {
        $comment = Comment::factory()->create(['published' => null]);
        $this->actingAs(User::factory()->create())
            ->postJson("/api/posts/{$comment->commentable_id}/comments", [
                'body' => 'Nová odpoveď', 'parent_id' => $comment->id,
            ])->assertJsonValidationErrors('parent_id');
    }

    public function test_prayer_publication_is_independent_of_fulfillment(): void
    {
        $open = Prayer::factory()->create();
        $fulfilled = Prayer::factory()->create(['fulfilled_at' => now()->subDay()]);
        Prayer::factory()->create(['published' => null]);
        Prayer::factory()->create(['published' => null, 'fulfilled_at' => now()]);

        $this->getJson('/api/prayers')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/prayers/fulfilled')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $fulfilled->id);
        $this->assertNull($open->fresh()->fulfilled_at);
        $this->assertNotNull($fulfilled->fresh()->fulfilled_at);
    }

    public function test_only_superadmin_can_edit_prayer_publication_and_fulfillment_is_preserved(): void
    {
        $owner = User::factory()->create();
        $canal = $owner->canals()->firstOrFail();
        $prayer = Prayer::factory()->create(['canal_id' => $canal->id, 'fulfilled_at' => '2026-09-01 10:00:00']);
        $url = route('profile.canals.prayers.update', [$canal->id, $prayer->id]);
        $data = ['title' => $prayer->title, 'body' => $prayer->body, 'published' => null];

        $this->actingAs($owner)->put($url, $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNotNull($prayer->fresh()->published);

        $owner->assignRole(['admin', 'superadmin']);
        $this->actingAs($owner->fresh())->put($url, $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($prayer->fresh()->published);
        $this->assertSame('2026-09-01 10:00:00', $prayer->fresh()->fulfilled_at->toDateTimeString());

        $this->put($url, array_replace($data, ['published' => '2026-09-02T11:30:45']))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('2026-09-02 11:30:45', $prayer->fresh()->published->toDateTimeString());
        $this->put($url, array_replace($data, ['published' => 'invalid']))->assertSessionHasErrors('published');
    }
}
