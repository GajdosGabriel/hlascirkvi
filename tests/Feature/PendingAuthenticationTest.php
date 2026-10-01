<?php

namespace Tests\Feature;

use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\User\ConfirmRegistration;
use App\Services\PendingUsers;
use App\Support\HumanCheck;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PendingAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // UserObserver::created volá assignRole('user').
        $this->seed(RolesSeeder::class);
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 200)]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function formData(array $overrides = []): array
    {
        return $overrides + [
            'first_name' => 'Ján',
            'last_name' => 'Novák',
            'email' => 'jan.novak@gmail.com',
            'password' => 'kostolna-vez-2026',
            'password_confirmation' => 'kostolna-vez-2026',
            HumanCheck::TRAP => '',
            HumanCheck::STAMP => $this->stampAgedBy(30),
        ];
    }

    /**
     * Pečiatka vykreslenia formulára posunutá do minulosti, aby test nemusel
     * čakať na uplynutie minimálneho času vypĺňania.
     */
    protected function stampAgedBy(int $seconds): string
    {
        return Crypt::encryptString((string) (time() - $seconds));
    }

    protected function registerAndCatchToken(): string
    {
        return $this->catchToken(fn () => $this->post('/register', $this->formData()));
    }

    /** Token z odkazu v práve odoslanom e-maile. */
    protected function catchToken(callable $action): string
    {
        Notification::fake();

        $action();

        $token = null;
        Notification::assertSentTo(
            PendingRegistration::firstOrFail(),
            ConfirmRegistration::class,
            function (ConfirmRegistration $notification, $channels, $notifiable) use (&$token) {
                preg_match('~/register/confirm/([A-Za-z0-9]{64})~', $notification->toMail($notifiable)->actionUrl, $m);
                $token = $m[1] ?? null;

                return $token !== null;
            }
        );

        return $token;
    }

    public function test_repeat_registration_from_foreign_browser_preserves_credentials_and_original_link(): void
    {
        $token = $this->registerAndCatchToken();
        $this->flushSession();
        $this->post('/register', $this->formData([
            'first_name' => 'Changed', 'password' => 'another-strong-password-2026',
            'password_confirmation' => 'another-strong-password-2026',
        ]))->assertRedirect(route('register.pending'));
        $pending = PendingRegistration::firstOrFail();
        $this->assertSame('Ján', $pending->first_name);
        $this->assertTrue(Hash::check('kostolna-vez-2026', $pending->password));
        $this->assertNotNull(PendingRegistration::findByToken($token));
        Notification::assertSentTimes(ConfirmRegistration::class, 1);
    }

    public function test_repeat_registration_from_same_browser_updates_credentials(): void
    {
        $token = $this->registerAndCatchToken();
        $this->post('/register', $this->formData([
            'first_name' => 'Changed', 'password' => 'another-strong-password-2026',
            'password_confirmation' => 'another-strong-password-2026',
        ]))->assertRedirect(route('register.pending'));
        $pending = PendingRegistration::firstOrFail();
        $this->assertSame('Changed', $pending->first_name);
        $this->assertTrue(Hash::check('another-strong-password-2026', $pending->password));
        $this->assertNotNull(PendingRegistration::findByToken($token));
    }

    public function test_pending_login_requires_password_and_does_not_send_or_authenticate(): void
    {
        $token = $this->registerAndCatchToken();
        $this->flushSession();
        Notification::fake();
        $this->post('/login', ['email' => 'jan.novak@gmail.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email')->assertSessionMissing('pending_registration');
        $this->post('/login', ['email' => ' JAN.NOVAK@gmail.com ', 'password' => 'kostolna-vez-2026'])
            ->assertRedirect(route('register.pending'))->assertSessionHas('pending_registration');
        $this->get(route('register.pending'))->assertOk()->assertSee('Poslať e-mail znova');
        $this->assertGuest();
        Notification::assertNothingSent();
        $this->assertNotNull(PendingRegistration::findByToken($token));
    }

    public function test_expired_pending_login_renews_confirmation_and_returns_json(): void
    {
        $old = $this->registerAndCatchToken();
        $this->travel(PendingRegistration::TTL_DAYS + 1)->days();
        Notification::fake();
        $this->postJson('/login', ['email' => 'jan.novak@gmail.com', 'password' => 'kostolna-vez-2026'])
            ->assertStatus(202)->assertJsonPath('redirect', route('register.pending'));
        $this->assertGuest();
        Notification::assertSentTimes(ConfirmRegistration::class, 1);
        $this->assertNull(PendingRegistration::findByToken($old));
    }

    public function test_legacy_login_restores_identity_only_after_confirmation(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => Hash::make('legacy-password')]);
        $user->forceFill(['email_verified_at' => null])->save();
        $canal = $user->fresh()->canal_id;
        app(PendingUsers::class)->move($user->id);
        $token = $this->catchToken(fn () => $this->post('/login', ['email' => $user->email, 'password' => 'legacy-password'])
            ->assertRedirect(route('register.pending')));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->get(route('register.confirm', $token))->assertRedirect(route('posts.index'));
        $this->assertAuthenticatedAs(User::findOrFail($user->id));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'canal_id' => $canal]);
        $this->assertTrue(Hash::check('legacy-password', User::findOrFail($user->id)->password));
    }

    public function test_blocked_legacy_login_does_not_send_confirmation(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null, 'disabled' => true, 'password' => Hash::make('legacy-password')]);
        app(PendingUsers::class)->move($user->id);
        $this->post('/login', ['email' => $user->email, 'password' => 'legacy-password'])
            ->assertSessionHasErrors('email')->assertSessionMissing('pending_registration');
        $this->assertGuest();
        Notification::assertNothingSent();
        $this->assertDatabaseCount('pending_registrations', 0);
    }

    public function test_registration_of_legacy_email_restores_the_original_account(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'jan.novak@gmail.com', 'email_verified_at' => null]);
        app(PendingUsers::class)->move($user->id);
        $token = $this->registerAndCatchToken();
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->get(route('register.confirm', $token))->assertRedirect(route('posts.index'));
        $this->assertAuthenticatedAs(User::findOrFail($user->id));
        $this->assertTrue(Hash::check('kostolna-vez-2026', User::findOrFail($user->id)->password));
        $this->assertDatabaseCount('pending_users', 0);
    }

    public function test_wrong_pending_password_is_throttled(): void
    {
        $this->registerAndCatchToken();
        $this->flushSession();
        Notification::fake();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/login', ['email' => 'jan.novak@gmail.com', 'password' => 'wrong'])->assertStatus(422);
        }
        $this->postJson('/login', ['email' => 'jan.novak@gmail.com', 'password' => 'kostolna-vez-2026'])->assertStatus(429);
        $this->assertGuest();
        Notification::assertNothingSent();
    }

    public function test_resend_limit_explains_next_step(): void
    {
        $this->registerAndCatchToken();
        PendingRegistration::firstOrFail()->update(['send_count' => PendingRegistration::MAX_SENDS]);
        $this->post(route('register.resend'))->assertRedirect(route('register.pending'))
            ->assertSessionHas('flash', fn ($message) => str_contains($message, 'limit'));
        $this->get(route('register.pending'))->assertOk()->assertSee('limit odoslaní')->assertDontSee('Poslať e-mail znova');
        Notification::assertSentTimes(ConfirmRegistration::class, 1);
    }
}
