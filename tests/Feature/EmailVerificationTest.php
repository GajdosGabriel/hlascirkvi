<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\User\ConfirmEmail;
use Database\Seeders\RolesSeeder;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Overenie e-mailovej adresy (App\Http\Controllers\Auth\VerificationController).
 * Odkaz z pošty zámerne nevyžaduje prihlásenie — nesie ho podpis v URL.
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    protected function unverifiedUser(): User
    {
        $user = User::factory()->create(['email' => 'jan.novak@gmail.com']);
        $user->email_verified_at = null;
        $user->save();

        return $user->fresh();
    }

    protected function verificationUrl(User $user, ?string $hash = null): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addDays(7), [
            'user' => $user->getKey(),
            'hash' => $hash ?? sha1($user->getEmailForVerification()),
        ]);
    }

    public function test_podpisany_odkaz_overi_adresu_aj_bez_prihlasenia(): void
    {
        Event::fake([Verified::class]);

        $user = $this->unverifiedUser();

        $this->get($this->verificationUrl($user))->assertRedirect(route('posts.index'));

        $this->assertNotNull($user->fresh()->email_verified_at);
        Event::assertDispatched(Verified::class);
    }

    public function test_odkaz_bez_podpisu_neoveri(): void
    {
        $user = $this->unverifiedUser();

        $this->get(route('verification.verify', [
            'user' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]))->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    /**
     * Odtlačok adresy v ceste znamená, že po zmene e-mailu staré odkazy
     * prestanú platiť.
     */
    public function test_odkaz_na_inu_adresu_neoveri(): void
    {
        $user = $this->unverifiedUser();

        $this->get($this->verificationUrl($user, sha1('niekto.iny@gmail.com')))
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_overenie_rovno_prihlasi(): void
    {
        $user = $this->unverifiedUser();

        $this->get($this->verificationUrl($user));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Odkaz otvorený na počítači, kde je prihlásený niekto iný, nesmie
     * prepnúť účet — overí len adresu.
     */
    public function test_odkaz_neprepne_prihlaseneho_na_cudzi_ucet(): void
    {
        $user = $this->unverifiedUser();
        $niekto = User::factory()->create(['email' => 'niekto.iny@gmail.com']);

        $this->actingAs($niekto)->get($this->verificationUrl($user));

        $this->assertAuthenticatedAs($niekto);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_opatovne_poslanie_emailu(): void
    {
        Notification::fake();

        $user = $this->unverifiedUser();

        $this->actingAs($user)->post(route('verification.resend'))->assertRedirect();

        Notification::assertSentTo($user, ConfirmEmail::class);
    }

    public function test_overeny_ucet_dalsi_email_nedostane(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->markEmailAsVerified();

        $this->actingAs($user)->post(route('verification.resend'))
            ->assertRedirect(route('posts.index'));

        Notification::assertNothingSent();
    }

    public function test_stranka_s_vyzvou_je_len_pre_prihlasenych(): void
    {
        $this->get(route('verification.notice'))->assertRedirect(route('login'));

        $this->actingAs($this->unverifiedUser())
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Pozrite si schránku');
    }
}
