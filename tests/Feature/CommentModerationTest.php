<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\Comments\InappropriateComment;
use App\Services\CommentModeration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommentModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }


    public function test_hides_abuse_on_create_and_edit_and_notifies_once(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id, 'body' => 'Ty si úplný kokot.']);
        $this->assertNull($comment->published);
        $this->assertNotNull($comment->moderation_reason);
        Notification::assertSentToTimes($user, InappropriateComment::class, 1);
        $comment->update(['reply_to_guest' => false]);
        Notification::assertSentToTimes($user, InappropriateComment::class, 1);
        $clean = Comment::factory()->create(['user_id' => $user->id, 'body' => 'Nesúhlasím s názorom autora.']);
        $this->assertNotNull($clean->published);
        $clean->update(['body' => 'Zabijem ťa!']);
        $this->assertNull($clean->fresh()->published);
        Notification::assertSentToTimes($user, InappropriateComment::class, 2);
    }

    public function test_retrospective_review_is_idempotent(): void
    {
        Notification::fake();
        $comment = Comment::factory()->create(['body' => 'Pokojná diskusia.']);
        DB::table('comments')->where('id', $comment->id)->update(['body' => 'Postrieľajte ich všetkých.']);
        $this->artisan('comments:moderate')->assertSuccessful();
        $this->artisan('comments:moderate')->assertSuccessful();
        $this->assertNull($comment->fresh()->published);
        Notification::assertSentToTimes($comment->user, InappropriateComment::class, 1);
    }

    public function test_hidden_comment_is_not_public_and_does_not_notify_thread_participants(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $canal = \App\Models\Canal::factory()->create();
        $owner = User::factory()->create(['canal_id' => $canal->id]);
        $post = \App\Models\Post::factory()->create(['canal_id' => $canal->id]);
        $parent = Comment::factory()->create(['commentable_id' => $post->id, 'body' => 'Ďakujem za zamyslenie.']);
        $comment = app(\App\Services\PendingConfirmation::class)->publishComment($parent->commentable, $user, [
            'body' => 'Zabijem ťa!', 'parent_id' => $parent->id,
        ]);
        $this->assertNull($comment->published);
        Notification::assertSentTo($user, InappropriateComment::class);
        Notification::assertNotSentTo($parent->user, \App\Notifications\Comments\RepliedToComment::class);
        Notification::assertNotSentTo($owner, \App\Notifications\Comments\CreatedNewComment::class);
        $this->getJson(route('posts.comments.index', $parent->commentable_id))->assertOk()->assertJsonCount(1)->assertJsonPath('0.replies', []);
        $mail = (new InappropriateComment($comment->body, $comment->moderation_reason))->toMail($user);
        $this->assertSame('Váš komentár bol skrytý', $mail->subject);
    }

    public function test_does_not_hide_peaceful_discussion_of_violence(): void
    {
        $moderation = app(CommentModeration::class);
        $this->assertNull($moderation->reason('Odsudzujem násilie a vojnu. Modlime sa za obete.'));
        $this->assertNull($moderation->reason('Písmo hovorí o utrpení. Nesúhlasím s politikou vlády.'));
        $this->assertNotNull($moderation->reason('Ty k.o.k.o.t!'));
    }
}
