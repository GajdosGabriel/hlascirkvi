<?php

namespace Tests\Feature;

use App\Models\Prayer;
use App\Models\User;
use App\Notifications\Prayer\ConfirmFulfilledPrayer;
use App\Notifications\Prayer\PrayerFulfilledOrNotYet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Odkaz „Áno, modlitba bola vypočutá" z e-mailu PrayerFulfilledOrNotYet.
 * GET len ukáže potvrdenie (poštové skenery odkazy otvárajú samy), označí
 * až POST na tú istú podpísanú adresu.
 */
class PrayerFulfilledLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    /** Odkaz presne tak, ako ho dostal autor v e-maile. */
    private function mailLink(Prayer $prayer): string
    {
        $mail = (new PrayerFulfilledOrNotYet($prayer))->toMail($prayer->user ?? $this->admin);

        return $mail->actionUrl;
    }

    public function test_otvorenie_odkazu_modlitbu_neoznaci(): void
    {
        $prayer = Prayer::factory()->create(['title' => 'Za uzdravenie mamy']);
        $link = $this->mailLink($prayer);

        $this->get($link)->assertOk()
            ->assertSee('Bola modlitba vypočutá?')
            ->assertSee('Za uzdravenie mamy')
            ->assertSee('Áno, modlitba bola vypočutá');
        $this->get($link)->assertOk();

        $this->assertNull($prayer->fresh()->fulfilled_at);
        Notification::assertNothingSent();
    }

    public function test_potvrdenie_oznaci_raz_a_neprepise_datum(): void
    {
        $prayer = Prayer::factory()->create();
        $link = $this->mailLink($prayer);

        $this->post($link)->assertRedirect(route('modlitby.index'))
            ->assertSessionHas('flash');
        $fulfilledAt = $prayer->fresh()->fulfilled_at;
        $this->assertNotNull($fulfilledAt);

        $this->travel(2)->days();
        $this->post($link)->assertRedirect(route('modlitby.index'));
        $this->get($link)->assertOk()->assertSee('Modlitba je medzi vypočutými')
            ->assertDontSee('<form', false);

        $this->assertEquals($fulfilledAt, $prayer->fresh()->fulfilled_at);
        Notification::assertSentToTimes($this->admin, ConfirmFulfilledPrayer::class, 1);
    }

    public function test_uz_rozposlany_odkaz_ostava_platny(): void
    {
        $prayer = Prayer::factory()->create();
        // Rovnaký tvar, aký mali e-maily pred zmenou (signedRoute bez expirácie).
        $old = URL::signedRoute('prayer.fulfilledAt', ['prayer' => $prayer->id]);

        $this->get($old)->assertOk();
        $this->post($old)->assertRedirect(route('modlitby.index'));
        $this->assertNotNull($prayer->fresh()->fulfilled_at);
    }

    public function test_bez_podpisu_nic(): void
    {
        $prayer = Prayer::factory()->create();
        $url = route('prayer.fulfilledAt', $prayer);

        $this->get($url)->assertForbidden();
        $this->post($url)->assertForbidden();
        $this->post($this->mailLink(Prayer::factory()->create()).'x')->assertForbidden();

        $this->assertNull($prayer->fresh()->fulfilled_at);
    }
}
