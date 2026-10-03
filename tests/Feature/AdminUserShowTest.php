<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Comment;
use App\Models\SystemLog;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminUserShowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
    }

    public function test_detail_shows_profile_activity_and_only_related_events(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Ján', 'email' => 'detail@example.com',
            'description' => '<script>profileSecret()</script>',
            'last_login_ip' => '192.0.2.42', 'last_login_via' => 'google',
            'last_login_at' => now(), 'status_changed_by' => $this->admin->id,
            'status_changed_at' => now(), 'newsletter_unsubscribed_at' => now(),
        ]);
        $canal = Canal::factory()->create(['title' => 'Detailový kanál']);
        $user->canals()->attach($canal);
        $user->update(['canal_id' => $canal->id]);
        Comment::factory()->create(['user_id' => $user->id, 'body' => 'Komentár detailu']);
        SystemLog::create(['channel' => 'auth', 'event' => 'auth.login', 'user_id' => $user->id, 'message' => 'Prihlásenie detailu']);
        SystemLog::create(['channel' => 'mail', 'event' => 'mail.sent', 'recipient' => $user->email, 'message' => 'E-mail detailu']);
        SystemLog::create(['channel' => 'auth', 'event' => 'auth.login', 'user_id' => $this->admin->id, 'message' => 'Cudzia udalosť']);

        $this->actingAs($this->admin)->get(route('admin.user.show', $user->id))
            ->assertOk()->assertSee('detail@example.com')->assertSee('192.0.2.42')
            ->assertSee('Google')->assertSee('Detailový kanál')->assertSee('Komentár detailu')
            ->assertSee('Prihlásenie detailu')->assertSee('E-mail detailu')->assertDontSee('Cudzia udalosť')
            ->assertSee('&lt;script&gt;profileSecret()&lt;/script&gt;', false)
            ->assertDontSee('<script>profileSecret()</script>', false)
            ->assertDontSee($user->password, false)->assertDontSee($user->remember_token, false);
    }

    public function test_deleted_and_empty_users_have_a_readable_detail(): void
    {
        $user = User::factory()->create(['last_login_at' => null]);
        $user->delete();
        $this->actingAs($this->admin)->get(route('admin.user.show', $user->id))
            ->assertOk()->assertSee('Zrušený účet')->assertSee('Bez záznamu')
            ->assertSee('Žiadne komentáre.')->assertDontSee(route('admin.user.edit', $user->id), false);
        $this->actingAs($this->admin)->get(route('admin.user.show', 999999))->assertNotFound();
    }

    public function test_detail_is_restricted_to_superadmins(): void
    {
        $user = User::factory()->create();
        $this->get(route('admin.user.show', $user->id))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('admin.user.show', $this->admin->id))->assertRedirect('/');
        $user->assignRole('admin');
        $this->actingAs($user)->get(route('admin.user.show', $this->admin->id))->assertRedirect('/');
    }

    public function test_database_sessions_exclude_expired_and_other_users_without_exposing_secrets(): void
    {
        config(['session.driver' => 'database', 'session.lifetime' => 120]);
        $user = User::factory()->create();
        foreach ([
            ['id' => 'secret-active-session', 'user_id' => $user->id, 'last_activity' => now()->timestamp, 'ip_address' => '192.0.2.10'],
            ['id' => 'secret-expired-session', 'user_id' => $user->id, 'last_activity' => now()->subHours(3)->timestamp, 'ip_address' => '192.0.2.11'],
            ['id' => 'secret-other-session', 'user_id' => $this->admin->id, 'last_activity' => now()->timestamp, 'ip_address' => '192.0.2.12'],
        ] as $session) {
            DB::table('sessions')->insert($session + ['user_agent' => 'Detail test browser', 'payload' => 'secret-session-payload']);
        }
        $this->actingAs($this->admin)->get(route('admin.user.show', $user->id))
            ->assertOk()->assertSee('192.0.2.10')->assertSee('Detail test browser')
            ->assertDontSee('192.0.2.11')->assertDontSee('192.0.2.12')
            ->assertDontSee('secret-active-session')->assertDontSee('secret-session-payload');
    }
}
