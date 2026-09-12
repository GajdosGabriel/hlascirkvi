<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\User\ConfirmEmail;
use App\Support\HumanCheck;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
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
            ->assertSee('Pokračovať cez Google')
            // Pole pasce musí byť v stránke, inak sa nemá čo kontrolovať.
            ->assertSee('name="'.HumanCheck::TRAP.'"', false)
            ->assertDontSee('7 plus 3');
    }

    public function test_registracia_zalozi_ucet_a_posle_overovaci_email(): void
    {
        Notification::fake();

        $this->post('/register', $this->formData())
            ->assertRedirect(route('verification.notice'));

        $user = User::whereEmail('jan.novak@gmail.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, ConfirmEmail::class);
    }

    /**
     * Preklepy v doméne opravuje App\Services\EmailSanitizer. Doteraz sa
     * nemal kde uplatniť, teraz beží ešte pred validáciou.
     */
    public function test_preklep_v_domene_sa_opravi(): void
    {
        Notification::fake();

        $this->post('/register', $this->formData(['email' => ' Jan.Novak@gmail.CON ']));

        $this->assertDatabaseHas('users', ['email' => 'jan.novak@gmail.com']);
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
