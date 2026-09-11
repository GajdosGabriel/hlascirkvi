<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

/**
 * Prihlásenie cez Google (App\Http\Controllers\Auth\AuthController).
 * Socialite je namockovaný — do Googlu sa naozaj nevolá.
 */
class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // UserObserver::created volá assignRole('user').
        $this->seed(RolesSeeder::class);
    }

    protected function googleUser(array $raw): SocialiteUser
    {
        $raw += [
            'sub' => '1234567890',
            'email' => 'jan.novak@gmail.com',
            'email_verified' => true,
            'name' => 'Ján Novák',
            'given_name' => 'Ján',
            'family_name' => 'Novák',
        ];

        return (new SocialiteUser)->setRaw($raw)->map([
            'id' => $raw['sub'],
            'name' => $raw['name'],
            'email' => $raw['email'],
        ]);
    }

    protected function mockGoogle($user): void
    {
        $provider = Mockery::mock(Provider::class);
        $user instanceof \Throwable
            ? $provider->shouldReceive('user')->andThrow($user)
            : $provider->shouldReceive('user')->andReturn($user);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_presmeruje_na_google(): void
    {
        config(['services.google.client_id' => 'test-client']);

        $response = $this->get('/auth/google');

        $response->assertRedirect();
        $this->assertStringStartsWith('https://accounts.google.com/', $response->headers->get('Location'));
        $this->assertStringContainsString(urlencode('/auth/google/callback'), $response->headers->get('Location'));
    }

    public function test_novy_pouzivatel_sa_zalozi_a_prihlasi(): void
    {
        $this->mockGoogle($this->googleUser([]));

        $this->get('/auth/google/callback')->assertRedirect('/');

        $user = User::whereEmail('jan.novak@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Ján', $user->first_name);
        $this->assertSame('Novák', $user->last_name);
        $this->assertNotNull($user->email_verified_at);

        foreach (['8', '9', '10'] as $weak) {
            $this->assertFalse(Hash::check($weak, $user->password));
        }
    }

    public function test_existujuci_ucet_sa_sparuje_podla_emailu(): void
    {
        $existing = User::factory()->create(['email' => 'jan.novak@gmail.com']);
        $this->mockGoogle($this->googleUser([]));

        $this->get('/auth/google/callback')->assertRedirect('/');

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::whereEmail('jan.novak@gmail.com')->count());
    }

    public function test_neovereny_email_neprihlasi(): void
    {
        User::factory()->create(['email' => 'jan.novak@gmail.com']);
        $this->mockGoogle($this->googleUser(['email_verified' => false]));

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_zrusene_prihlasenie_skonci_hlaskou_nie_chybou(): void
    {
        $this->mockGoogle(new InvalidStateException);

        $this->get('/auth/google/callback?error=access_denied')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_blokovany_ucet_sa_neprihlasi(): void
    {
        $user = User::factory()->create(['email' => 'jan.novak@gmail.com']);
        $user->disabled = true;
        $user->save();
        $this->mockGoogle($this->googleUser([]));

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }
}
