<?php

namespace Tests\Feature;

use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\User\ConfirmRegistration;
use App\Support\HumanCheck;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Registrácia formulárom (App\Http\Controllers\Auth\RegisterController)
 * vrátane neviditeľnej kontroly z App\Support\HumanCheck, ktorá nahradila
 * počítanie „7 plus 3".
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // UserObserver::created volá assignRole('user').
        $this->seed(RolesSeeder::class);
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

    public function test_formular_sa_zobrazi(): void
    {
        $this->get('/register')
            ->assertOk()
            // Tlačidlo Google závisí od GOOGLE_CLIENT_ID, overuje ho SocialLoginTest.
            ->assertSee('Pokračovať cez Facebook')
            // Pole pasce musí byť v stránke, inak sa nemá čo kontrolovať.
            ->assertSee('name="'.HumanCheck::TRAP.'"', false)
            ->assertDontSee('7 plus 3');
    }

    /**
     * Formulár ešte nezakladá účet ani kanál — len čakajúcu registráciu
     * a e-mail s odkazom.
     */
    public function test_registracia_len_caka_na_potvrdenie(): void
    {
        Notification::fake();

        $this->post('/register', $this->formData())
            ->assertRedirect(route('register.pending'));

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('canals', 0);

        $pending = PendingRegistration::whereEmail('jan.novak@gmail.com')->firstOrFail();
        $this->assertTrue(Hash::check('kostolna-vez-2026', $pending->password));

        Notification::assertSentTo($pending, ConfirmRegistration::class);

        $this->get(route('register.pending'))->assertOk()->assertSee('jan.novak@gmail.com');
    }

    public function test_potvrdenie_zalozi_overeny_ucet_s_kanalom(): void
    {
        $token = $this->registerAndCatchToken();

        $this->get(route('register.confirm', $token))->assertRedirect(route('posts.index'));

        $user = User::whereEmail('jan.novak@gmail.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('kostolna-vez-2026', $user->password));
        $this->assertNotNull($user->canal_id);
        $this->assertSame(1, $user->canals()->count());
        $this->assertDatabaseCount('pending_registrations', 0);
    }

    public function test_odkaz_sa_neda_pouzit_dvakrat(): void
    {
        $token = $this->registerAndCatchToken();

        $this->get(route('register.confirm', $token));
        auth()->logout();

        $this->get(route('register.confirm', $token))->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 1);
    }

    public function test_vyprsany_odkaz_ucet_nezalozi(): void
    {
        $token = $this->registerAndCatchToken();

        $this->travel(PendingRegistration::TTL_DAYS + 1)->days();

        $this->get(route('register.confirm', $token))->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_opakovane_odoslanie_nezasype_schranku(): void
    {
        Notification::fake();

        $this->post('/register', $this->formData());
        $this->post('/register', $this->formData());
        $this->post(route('register.resend'));

        $this->assertDatabaseCount('pending_registrations', 1);
        Notification::assertSentTimes(ConfirmRegistration::class, 1);

        $this->travel(PendingRegistration::RESEND_AFTER_SECONDS + 1)->seconds();
        $this->post(route('register.resend'));

        Notification::assertSentTimes(ConfirmRegistration::class, 2);
    }

    /** Novší odkaz zneplatní starší. */
    public function test_znova_poslany_email_zneplatni_stary_odkaz(): void
    {
        $old = $this->registerAndCatchToken();

        $this->travel(PendingRegistration::RESEND_AFTER_SECONDS + 1)->seconds();
        $new = $this->catchToken(fn () => $this->post(route('register.resend')));

        $this->assertNotSame($old, $new);
        $this->get(route('register.confirm', $old))->assertRedirect(route('login'));
        $this->get(route('register.confirm', $new))->assertRedirect(route('posts.index'));
    }

    public function test_existujuca_adresa_neprejde(): void
    {
        User::factory()->create(['email' => 'jan.novak@gmail.com']);

        $this->post('/register', $this->formData())->assertSessionHasErrors('email');
        $this->assertDatabaseCount('pending_registrations', 0);
    }

    /** Účet mohol medzitým vzniknúť inak (Google) — ten sa neprepisuje. */
    public function test_potvrdenie_neprepise_ucet_vzniknuty_medzitym(): void
    {
        $token = $this->registerAndCatchToken();
        $existing = User::factory()->create(['email' => 'jan.novak@gmail.com']);

        $this->get(route('register.confirm', $token))->assertRedirect(route('login'));

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($existing->password, $existing->fresh()->password);
    }

    public function test_po_troch_dnoch_pride_jedna_pripomienka(): void
    {
        $old = $this->registerAndCatchToken();
        $expires = PendingRegistration::firstOrFail()->expires_at;

        $this->travelTo(now()->addDays(PendingRegistration::REMIND_AFTER_DAYS)->subHour());
        $this->artisan('registrations:remind');
        Notification::assertSentTimes(ConfirmRegistration::class, 1);

        $this->travel(2)->hours();
        $new = $this->catchToken(fn () => $this->artisan('registrations:remind'));

        // Nový odkaz, starý neplatí, platnosť sa nepredĺžila.
        $this->assertNotSame($old, $new);
        $this->assertTrue($expires->equalTo(PendingRegistration::firstOrFail()->expires_at));
        $this->get(route('register.confirm', $old))->assertRedirect(route('login'));

        // Druhá pripomienka už nepríde.
        Notification::fake();
        $this->travel(1)->days();
        $this->artisan('registrations:remind');
        Notification::assertNothingSent();

        $this->get(route('register.confirm', $new))->assertRedirect(route('posts.index'));
    }

    public function test_expirovane_registracie_zmaze_prune(): void
    {
        $this->registerAndCatchToken();
        $this->travel(PendingRegistration::TTL_DAYS + 1)->days();

        $this->artisan('model:prune', ['--model' => [PendingRegistration::class]]);

        $this->assertDatabaseCount('pending_registrations', 0);
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

    /**
     * Preklepy v doméne opravuje App\Services\EmailSanitizer. Doteraz sa
     * nemal kde uplatniť, teraz beží ešte pred validáciou.
     */
    public function test_preklep_v_domene_sa_opravi(): void
    {
        Notification::fake();

        $this->post('/register', $this->formData(['email' => ' Jan.Novak@gmail.CON ']));

        $this->assertDatabaseHas('pending_registrations', ['email' => 'jan.novak@gmail.com']);
    }

    public function test_vyplnena_pasca_registraciu_zastavi(): void
    {
        Notification::fake();

        $this->post('/register', $this->formData([HumanCheck::TRAP => 'https://spam.example']))
            ->assertSessionHasErrors(HumanCheck::STAMP);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_okamzite_odoslanie_registraciu_zastavi(): void
    {
        Notification::fake();

        $this->post('/register', $this->formData([HumanCheck::STAMP => $this->stampAgedBy(0)]))
            ->assertSessionHasErrors(HumanCheck::STAMP);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_chybajuca_peciatka_registraciu_zastavi(): void
    {
        $data = $this->formData();
        unset($data[HumanCheck::STAMP]);

        $this->post('/register', $data)->assertSessionHasErrors(HumanCheck::STAMP);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_kratke_heslo_neprejde(): void
    {
        $this->post('/register', $this->formData([
            'password' => 'kratke',
            'password_confirmation' => 'kratke',
        ]))->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }
}
