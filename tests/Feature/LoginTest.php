<?php

namespace Tests\Feature;

use App\Enums\ModelStatus;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Prihlásenie formulárom. Stránka ho dovtedy odosielala cez axios
 * z LoginForm.vue - hlášky si skladala sama a vzhľad sa rozchádzal so
 * zvyškom prihlasovacích stránok. Odteraz je to bežný POST formulár
 * a chyby vracia AuthenticatesUsers.
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // UserObserver::created volá assignRole('user').
        $this->seed(RolesSeeder::class);
    }

    public function test_formular_sa_zobrazi(): void
    {
        $this->get('/login')
            ->assertOk()
            // Tlačidlo Google závisí od GOOGLE_CLIENT_ID, overuje ho SocialLoginTest.
            ->assertSee('Pokračovať cez Facebook')
            ->assertSee('Zabudnuté heslo?')
            ->assertSee('action="'.route('login').'"', false);
    }

    public function test_spravne_udaje_prihlasia(): void
    {
        $user = User::factory()->create([
            'email' => 'jan.novak@gmail.com',
            'password' => Hash::make('kostolna-vez-2026'),
        ]);

        $this->post('/login', [
            'email' => 'jan.novak@gmail.com',
            'password' => 'kostolna-vez-2026',
        ])->assertRedirect(route('profile.dashboard'));

        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertSame('password', $user->last_login_via);
        $this->assertSame('127.0.0.1', $user->last_login_ip);
    }

    public function test_blokovany_ucet_dostane_vysvetlenie_a_neprihlasi_sa(): void
    {
        $user = User::factory()->create([
            'email' => 'jan.novak@gmail.com',
            'password' => Hash::make('kostolna-vez-2026'),
            'status' => ModelStatus::Blocked,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'kostolna-vez-2026',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('error', $user->accountAccessMessage());

        $this->assertGuest();
        $this->assertNull($user->refresh()->last_login_at);
    }

    public function test_zlym_heslom_sa_stav_blokovaneho_uctu_neprezradi(): void
    {
        $user = User::factory()->create([
            'email' => 'jan.novak@gmail.com',
            'password' => Hash::make('kostolna-vez-2026'),
            'status' => ModelStatus::Blocked,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'zle-heslo',
        ])->assertSessionMissing('error')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_neaktivny_prihlaseny_ucet_sa_odhlasi_na_lubovolnej_stranke(): void
    {
        $user = User::factory()->create(['status' => ModelStatus::Archived]);

        $this->actingAs($user)->get('/')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', $user->accountAccessMessage());

        $this->assertGuest();
    }

    public function test_nespravne_heslo_vrati_hlasku_pri_poli(): void
    {
        User::factory()->create([
            'email' => 'jan.novak@gmail.com',
            'password' => Hash::make('kostolna-vez-2026'),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'jan.novak@gmail.com',
                'password' => 'zle-heslo',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * Zaškrtnuté "Zostať prihlásený" musí prísť ako `remember` - pod iným
     * názvom by ho AuthenticatesUsers zahodilo a prihlásenie by platilo len
     * do zatvorenia prehliadača.
     *
     * Poznávacím znakom je cookie pripomínača, nie remember_token v databáze:
     * ten Laravel dopĺňa iba prázdnemu účtu, takže by pri už vyplnenom
     * stĺpci vyšiel test nazeleno aj bez zaškrtnutia.
     */
    public function test_zostat_prihlaseny_posle_cookie_pripominaca(): void
    {
        User::factory()->create([
            'email' => 'jan.novak@gmail.com',
            'password' => Hash::make('kostolna-vez-2026'),
        ]);

        $guard = Auth::guard('web');
        if (! $guard instanceof SessionGuard) {
            $this->fail('Web guard musí používať session driver.');
        }
        $recaller = $guard->getRecallerName();

        $with = $this->post('/login', [
            'email' => 'jan.novak@gmail.com',
            'password' => 'kostolna-vez-2026',
            'remember' => 'on',
        ]);

        $this->assertNotNull($with->getCookie($recaller));

        $this->post('/logout');

        $without = $this->post('/login', [
            'email' => 'jan.novak@gmail.com',
            'password' => 'kostolna-vez-2026',
        ]);

        $this->assertNull($without->getCookie($recaller));
    }
}
