<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Comment;
use App\Models\PendingComment;
use App\Models\PendingPrayer;
use App\Models\Post;
use App\Models\Prayer;
use App\Models\User;
use App\Notifications\Comments\ConfirmComment;
use App\Notifications\Comments\CreatedNewComment;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Komentár bez overenej adresy čaká v `pending_comments`, kým autor
 * nepotvrdí e-mail. Do `users` sa nezapisuje nič, kým adresa nie je overená.
 */
class PendingCommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        Notification::fake();

        $this->owner = User::factory()->create();
        $canal = Canal::factory()->create();
        $this->owner->canals()->attach($canal);
        $this->owner->update(['canal_id' => $canal->id]);

        $this->post = Post::factory()->create(['canal_id' => $canal->id]);
    }

    private function anonymousComment(string $email = 'navstevnik@example.com'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson("/api/posts/{$this->post->id}/comments", [
            'body' => 'Komentár od návštevníka.',
            'email' => $email,
        ]);
    }

    private function tokenFor(PendingComment $pending): string
    {
        $token = null;

        Notification::assertSentTo($pending, ConfirmComment::class, function ($n) use (&$token) {
            $token = (fn () => $this->token)->call($n);

            return true;
        });

        return $token;
    }

    public function test_potvrdenie_zalozi_overeny_ucet_a_zverejni(): void
    {
        $this->anonymousComment()->assertAccepted();
        Notification::assertNothingSentTo($this->owner, CreatedNewComment::class);

        $token = $this->tokenFor(PendingComment::sole());

        $this->get(route('comments.confirm', $token))->assertRedirect($this->post->path());

        $user = User::whereEmail('navstevnik@example.com')->sole();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, PendingComment::count());

        $comment = Comment::sole();
        $this->assertSame($user->id, (int) $comment->user_id);
        $this->assertSame($this->post->id, (int) $comment->commentable_id);
        Notification::assertSentTo($this->owner, CreatedNewComment::class);
    }

    public function test_adresa_existujuceho_uctu_caka_na_potvrdenie(): void
    {
        $user = User::factory()->create(['email' => 'overeny@example.com']);

        $this->anonymousComment('overeny@example.com')->assertAccepted();
        $this->assertSame(0, Comment::count());

        $this->get(route('comments.confirm', $this->tokenFor(PendingComment::sole())));

        $this->assertSame($user->id, (int) Comment::sole()->user_id);
        $this->assertSame(1, User::whereEmail('overeny@example.com')->count());
    }

    public function test_prihlaseny_overeny_komentuje_hned(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson("/api/posts/{$this->post->id}/comments", ['body' => 'Komentár prihláseného.'])
            ->assertCreated();

        $this->assertSame(1, Comment::count());
        $this->assertSame(0, PendingComment::count());
    }

    public function test_prihlaseny_neovereny_caka(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->postJson("/api/posts/{$this->post->id}/comments", ['body' => 'Komentár neovereného.'])
            ->assertAccepted();

        $this->assertSame(0, Comment::count());
        $this->assertSame($user->email, PendingComment::sole()->email);
    }

    public function test_potvrdenie_zverejni_aj_modlitbu_s_tou_istou_adresou(): void
    {
        $this->anonymousComment();
        $this->postJson('/api/prayers', [
            'title' => 'Prosba o zdravie',
            'body' => 'Modlite sa prosím za moju rodinu.',
            'email' => 'navstevnik@example.com',
        ]);

        $this->get(route('comments.confirm', $this->tokenFor(PendingComment::sole())));

        $this->assertSame(1, Comment::count());
        $this->assertSame(1, Prayer::count());
        $this->assertSame(0, PendingPrayer::count());
    }

    public function test_odpoved_na_zmazany_komentar_ostane_hlavnym(): void
    {
        $parent = $this->post->comments()->create(['body' => 'Hlavný', 'user_id' => $this->owner->id]);

        $this->postJson("/api/posts/{$this->post->id}/comments", [
            'body' => 'Odpoveď návštevníka.',
            'email' => 'navstevnik@example.com',
            'parent_id' => $parent->id,
        ])->assertAccepted();

        $parent->delete();

        $this->get(route('comments.confirm', $this->tokenFor(PendingComment::sole())));

        $this->assertNull(Comment::where('body', 'Odpoveď návštevníka.')->sole()->parent_id);
    }
}
