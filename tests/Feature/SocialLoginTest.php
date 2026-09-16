<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Prihlásenie cez Google (App\Http\Controllers\Auth\AuthController::googleAuth).
 * Overenie ID tokenu cez tokeninfo je podvrhnuté — do Googlu sa naozaj nevolá.
 */
class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // UserObserver::created volá assignRole('user').
        $this->seed(RolesSeeder::class);

        config(['services.google.client_id' => 'test-client']);
    }

    protected function fakeTokenInfo(array $payload, int $status = 200): void
    {
        $payload += [
            'aud' => 'test-client',
            'sub' => '1234567890',
            'email' => 'jan.novak@gmail.com',
            'email_verified' => 'true',
            'name' => 'Ján Novák',
            'given_name' => 'Ján',
            'family_name' => 'Novák',
        ];

        Http::fake([
            'oauth2.googleapis.com/tokeninfo*' => Http::response($status === 200 ? $payload : ['error' => 'invalid_token'], $status),
            // SocialAvatar — fotka sa v testoch nesťahuje.
            '*' => Http::response('', 404),
        ]);
    }

    protected function postCredential(string $credential = 'id-token')
    {
        return $this->post('/auth/google', ['credential' => $credential]);
    }

    public function test_novy_pouzivatel_sa_zalozi_a_prihlasi(): void
    {
        $this->fakeTokenInfo([]);

        $this->postCredential()->assertRedirect('/');

        $user = User::whereEmail('jan.novak@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Ján', $user->first_name);
        $this->assertSame('Novák', $user->last_name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->last_login_at);
        $this->assertSame('google', $user->last_login_via);
        $this->assertSame('127.0.0.1', $user->last_login_ip);

        foreach (['8', '9', '10'] as $weak) {
            $this->assertFalse(Hash::check($weak, $user->password));
        }

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://oauth2.googleapis.com/tokeninfo')
            && $request['id_token'] === 'id-token');
    }

    public function test_existujuci_ucet_sa_sparuje_podla_emailu(): void
    {
        $existing = User::factory()->create(['email' => 'jan.novak@gmail.com']);
        $this->fakeTokenInfo([]);

        $this->postCredential()->assertRedirect('/');

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::whereEmail('jan.novak@gmail.com')->count());
    }

    public function test_meno_bez_given_name_sa_rozdeli(): void
    {
        $this->fakeTokenInfo(['given_name' => '', 'family_name' => '', 'name' => 'Mária Nová Kováčová']);

        $this->postCredential()->assertRedirect('/');

        $user = User::whereEmail('jan.novak@gmail.com')->firstOrFail();
        $this->assertSame('Mária', $user->first_name);
        $this->assertSame('Nová Kováčová', $user->last_name);
    }

    public function test_neovereny_email_neprihlasi(): void
    {
        User::factory()->create(['email' => 'jan.novak@gmail.com']);
        $this->fakeTokenInfo(['email_verified' => 'false']);

        $this->postCredential()
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_token_pre_inu_aplikaciu_neprihlasi(): void
    {
        User::factory()->create(['email' => 'jan.novak@gmail.com']);
        $this->fakeTokenInfo(['aud' => 'cudzia-aplikacia']);

        $this->postCredential()
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_neplatny_token_skonci_hlaskou_nie_chybou(): void
    {
        $this->fakeTokenInfo([], 400);

        $this->postCredential()
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_bez_tokenu_sa_google_nevola(): void
    {
        Http::fake();

        $this->post('/auth/google')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        Http::assertNothingSent();
        $this->assertGuest();
    }

    public function test_blokovany_ucet_sa_neprihlasi(): void
    {
        $user = User::factory()->create(['email' => 'jan.novak@gmail.com']);
        $user->disabled = true;
        $user->save();
        $this->fakeTokenInfo([]);

        $this->postCredential()
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_prihlasovacia_stranka_ukaze_tlacidlo_google(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="credential"', false)
            ->assertSee('data-client-id="test-client"', false)
            ->assertDontSee('/auth/google/callback', false);
    }

    public function test_bez_client_id_sa_tlacidlo_nezobrazi(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/register')
            ->assertOk()
            ->assertDontSee('name="credential"', false);
    }
}
