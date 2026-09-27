<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\PendingUsers;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PendingUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->seed(RolesSeeder::class);
    }

    public function test_move_preserves_content_and_confirmation_restores_original_identity(): void
    {
        $user = User::factory()->create();
        $canal = $user->canals()->firstOrFail();
        $user->forceFill(['email_verified_at' => null])->save();
        $comment = Comment::factory()->create(['user_id' => $user->id, 'user_name' => null]);
        $user->createToken('old-session');
        app(PendingUsers::class)->move($user->id);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('pending_users', ['id' => $user->id, 'email' => $user->email]);
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'user_id' => null, 'pending_user_id' => $user->id]);
        $this->assertDatabaseHas('canal_user', ['canal_id' => $canal->id, 'user_id' => null, 'pending_user_id' => $user->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
        $this->getJson('/api/posts/'.$comment->commentable_id.'/comments')->assertOk()->assertDontSee($user->email);

        $pending = new PendingRegistration(['email' => $user->email, 'first_name' => 'Potvrdené', 'last_name' => 'Meno', 'password' => bcrypt('new-password')]);
        $restored = app(EloquentUserRepository::class)->createFromPendingRegistration($pending);
        $this->assertSame($user->id, $restored->id);
        $this->assertTrue($restored->hasVerifiedEmail());
        $this->assertSame('Potvrdené', $restored->first_name);
        $this->assertSame($canal->id, $restored->canals()->first()->id);
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'user_id' => $user->id, 'pending_user_id' => null]);
        $this->assertDatabaseMissing('pending_users', ['id' => $user->id]);
    }

    public function test_verified_accounts_are_never_moved(): void
    {
        $user = User::factory()->create();
        app(PendingUsers::class)->move($user->id);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('pending_users', ['id' => $user->id]);
    }

    public function test_admin_sees_email_and_only_verified_site_comments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);
        $author = User::factory()->create(['first_name' => 'K•••m', 'last_name' => '', 'email' => 'krajcikova.martina.km@example.com']);
        Comment::factory()->create(['user_id' => $author->id, 'user_name' => 'K•••m', 'body' => 'Overený komentár']);
        $unverified = User::factory()->create(['email_verified_at' => null]);
        Comment::factory()->create(['user_id' => $unverified->id, 'body' => 'Nepotvrdený komentár']);
        Comment::factory()->create(['user_id' => null, 'source' => 'youtube', 'user_name' => 'YouTube autor', 'body' => 'Importovaný komentár']);
        $this->actingAs($admin)->get(route('admin.comment.index'))->assertOk()
            ->assertSee($author->email)->assertDontSee('K•••m')
            ->assertDontSee('Nepotvrdený komentár')->assertDontSee('Importovaný komentár');
        app(PendingUsers::class)->move($unverified->id);
        $this->get(route('admin.user.edit', $unverified->id))->assertRedirect(route('admin.user.index', ['pending' => 1]));
        $this->get(route('admin.user.index', ['pending' => 1]))->assertOk()->assertSee($unverified->email)->assertSee($unverified->uuid);
    }

    public function test_blocked_pending_account_cannot_bypass_restriction(): void
    {
        $user = User::factory()->create(['email_verified_at' => null, 'disabled' => true]);
        app(PendingUsers::class)->move($user->id);
        try {
            app(PendingUsers::class)->restoreVerified($user->email);
            $this->fail('Blocked account was restored');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
            $this->assertDatabaseHas('pending_users', ['id' => $user->id]);
        }
    }
}
