<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\PendingFavorite;
use App\Models\Prayer;
use App\Models\User;
use App\Notifications\User\ConfirmFavorite;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * „Pripojiť sa k modlitbe" a odber kanála od neprihláseného čakajú
 * v `pending_favorites`, kým autor nepotvrdí e-mail. Do `users` sa
 * nezapisuje nič, kým adresa nie je overená.
 */
class PendingFavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        Notification::fake();
    }

    private function join(Prayer $prayer, string $email = 'pripojeny@example.com'): \Illuminate\Testing\TestResponse
    {
        return $this->putJson("/favorites/{$prayer->id}", [
            'model' => 'Prayer',
            'model_id' => $prayer->id,
            'email' => $email,
        ]);
    }

    private function tokenFor(PendingFavorite $pending): string
    {
        $token = null;

        Notification::assertSentTo($pending, ConfirmFavorite::class, function ($n) use (&$token) {
            $token = (fn () => $this->token)->call($n);

            return true;
        });

        return $token;
    }

    public function test_pripojenie_k_modlitbe_caka_na_potvrdenie(): void
    {
        $prayer = Prayer::factory()->create();

        $this->join($prayer)->assertAccepted()->assertJson(['pending' => true]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'pripojeny@example.com']);
        $this->assertSame(0, $prayer->favorites()->count());
        Notification::assertSentTo(PendingFavorite::sole(), ConfirmFavorite::class);
    }

    public function test_opakovane_pripojenie_neposle_druhy_email(): void
    {
        $prayer = Prayer::factory()->create();

        $this->join($prayer)->assertAccepted();
        $this->join($prayer)->assertAccepted();

        $this->assertSame(1, PendingFavorite::count());
        Notification::assertSentToTimes(PendingFavorite::sole(), ConfirmFavorite::class, 1);
    }

    public function test_potvrdenie_zalozi_overeny_ucet_a_zapocita(): void
    {
        $prayer = Prayer::factory()->create();
        $this->join($prayer);

        $this->get(route('favorites.confirm', $this->tokenFor(PendingFavorite::sole())))
            ->assertRedirect(route('modlitby.index'));

        $user = User::whereEmail('pripojeny@example.com')->sole();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($prayer->favorites()->whereUserId($user->id)->exists());
        $this->assertSame(0, PendingFavorite::count());
    }

    public function test_potvrdenie_nezrusi_existujuce_pripojenie(): void
    {
        $user = User::factory()->create(['email' => 'overeny@example.com']);
        $prayer = Prayer::factory()->create();
        $prayer->favorites()->create(['user_id' => $user->id]);

        $this->join($prayer, 'overeny@example.com')->assertAccepted();
        $this->get(route('favorites.confirm', $this->tokenFor(PendingFavorite::sole())));

        $this->assertSame(1, $prayer->favorites()->whereUserId($user->id)->count());
    }

    public function test_odber_kanala_bez_prihlasenia(): void
    {
        $canal = Canal::factory()->create();

        $this->postJson("/api/organizations/{$canal->id}/favorites")->assertJsonValidationErrors('email');

        $this->postJson("/api/organizations/{$canal->id}/favorites", ['email' => 'odber@example.com'])
            ->assertAccepted();

        $this->assertDatabaseMissing('users', ['email' => 'odber@example.com']);

        $this->get(route('favorites.confirm', $this->tokenFor(PendingFavorite::sole())))
            ->assertRedirect(route('organizations.show', $canal));

        $user = User::whereEmail('odber@example.com')->sole();
        $this->assertTrue($canal->favorites()->whereUserId($user->id)->exists());
    }

    public function test_prihlaseny_sa_pripaja_hned(): void
    {
        $user = User::factory()->create();
        $prayer = Prayer::factory()->create();

        $this->actingAs($user)->putJson("/favorites/{$prayer->id}", [
            'model' => 'Prayer',
            'model_id' => $prayer->id,
        ])->assertOk();

        $this->assertTrue($prayer->favorites()->whereUserId($user->id)->exists());
        $this->assertSame(0, PendingFavorite::count());
    }
}
