<?php

namespace Tests\Feature;

use App\Models\PendingPrayer;
use App\Models\Prayer;
use App\Models\User;
use App\Notifications\Prayer\ConfirmPrayer;
use App\Notifications\Prayer\NewPrayer;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Modlitba od neprihláseného čaká v `pending_prayers`, kým autor nepotvrdí
 * e-mail. Do `users` sa nezapisuje nič, kým adresa nie je overená.
 */
class PrayerPublishingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        Notification::fake();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    private function anonymousPrayer(string $email = 'modlitba@example.com'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/prayers', [
            'title' => 'Prosba o zdravie',
            'body' => 'Modlite sa prosím za moju rodinu.',
            'email' => $email,
            'form_ts' => $this->humanStamp(),
        ]);
    }

    /** Token z odoslaného e-mailu (v databáze je len jeho hash). */
    private function tokenFor(PendingPrayer $pending): string
    {
        $token = null;

        Notification::assertSentTo($pending, ConfirmPrayer::class, function ($n) use (&$token) {
            $token = (fn () => $this->token)->call($n);

            return true;
        });

        return $token;
    }

    public function test_neprihlaseny_nezalozi_ucet_ani_modlitbu(): void
    {
        $this->anonymousPrayer()->assertCreated()->assertJson(['pending' => true]);

        $this->assertDatabaseMissing('users', ['email' => 'modlitba@example.com']);
        $this->assertSame(0, Prayer::count());
        $this->assertGuest();

        $pending = PendingPrayer::sole();
        Notification::assertSentTo($pending, ConfirmPrayer::class);
        Notification::assertNothingSentTo($this->admin, NewPrayer::class);
    }

    public function test_potvrdenie_zalozi_overeny_ucet_a_zverejni(): void
    {
        $this->anonymousPrayer();
        $token = $this->tokenFor(PendingPrayer::sole());

        $this->get(route('modlitby.confirm', $token))->assertRedirect(route('modlitby.index'));

        $user = User::whereEmail('modlitba@example.com')->sole();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, PendingPrayer::count());

        $prayer = Prayer::sole();
        $this->assertSame($user->fresh()->canal_id, $prayer->canal_id);
        $this->getJson('/api/prayers')->assertJsonCount(1, 'data');
        Notification::assertSentTo($this->admin, NewPrayer::class);
    }

    public function test_adresa_overeneho_uctu_bez_prihlasenia_caka_na_potvrdenie(): void
    {
        User::factory()->create(['email' => 'overeny@example.com']);

        // Odpoveď je rovnaká ako pri neznámej adrese — neprezrádza účet.
        $this->anonymousPrayer('overeny@example.com')->assertCreated()->assertJson(['pending' => true]);

        $this->assertGuest();
        $this->assertSame(0, Prayer::count());
        $this->assertSame(1, PendingPrayer::count());
    }

    public function test_prihlaseny_overeny_zverejni_hned(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/prayers', [
            'title' => 'Prosba o zdravie',
            'body' => 'Modlite sa prosím za moju rodinu.',
        ])->assertCreated()->assertJson(['pending' => false, 'prayer' => ['title' => 'Prosba o zdravie']])
            ->assertJsonMissingPath('prayer.published');

        $this->assertSame($user->fresh()->canal_id, Prayer::sole()->canal_id);
    }

    public function test_hosta_bez_peciatky_a_s_dlhym_textom_odmietne(): void
    {
        $this->postJson('/api/prayers', [
            'title' => 'Prosba o zdravie',
            'body' => 'Modlite sa prosím za moju rodinu.',
            'email' => 'modlitba@example.com',
        ])->assertJsonValidationErrors('form_ts');

        $this->postJson('/api/prayers', [
            'title' => str_repeat('a', 256),
            'body' => str_repeat('b', 3001),
            'email' => 'modlitba@example.com',
            'form_ts' => $this->humanStamp(),
        ])->assertJsonValidationErrors(['title', 'body']);

        $this->assertSame(0, PendingPrayer::count());
    }

    public function test_pocet_cakajucich_na_adresu_je_obmedzeny(): void
    {
        foreach (range(1, PendingPrayer::MAX_PER_EMAIL) as $i) {
            $this->anonymousPrayer()->assertCreated();
        }

        $this->anonymousPrayer()->assertJsonValidationErrors('email');
    }

    public function test_pripomienka_po_troch_dnoch_raz_na_adresu(): void
    {
        $this->anonymousPrayer();
        $this->anonymousPrayer();

        $this->travel(PendingPrayer::REMIND_AFTER_DAYS)->days();
        $this->artisan('pending:remind')->assertSuccessful();
        $this->artisan('pending:remind')->assertSuccessful();

        $reminders = 0;
        foreach (PendingPrayer::all() as $pending) {
            $this->assertNotNull($pending->reminded_at);
            $reminders += Notification::sent($pending, ConfirmPrayer::class)
                ->filter(fn ($n) => (fn () => $this->reminder)->call($n))->count();
        }
        $this->assertSame(1, $reminders);
    }
}
