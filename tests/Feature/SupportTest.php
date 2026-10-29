<?php

namespace Tests\Feature;

use App\Enums\SupportStatus;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\Support\SupportTicketCreated;
use App\Notifications\Support\SupportTicketReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Formulár „Napíšte nám" do 10/2026 len poslal správcovi e-mail. Teraz
 * zakladá vlákno podpory — správa ostane zaevidovaná, správca odpovedá
 * v administrácii a používateľ odpoveď vidí v nástenke.
 */
class SupportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
        $this->user = User::factory()->create();
    }

    public function test_host_nemoze_napisat(): void
    {
        $this->post(route('profile.support.store'), ['subject' => 'Pozdrav', 'body' => 'Ahoj'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_sprava_z_paty_zalozi_vlakno_a_upozorni_spravcu(): void
    {
        Notification::fake();

        $this->actingAs($this->user)
            ->post(route('profile.support.store'), [
                'subject' => '  Nahrávanie videa ',
                'body' => "Nejde mi nahrať video.\nĎakujem",
                'page_url' => 'https://hlascirkvi.sk/dashboard',
            ])
            ->assertRedirect();

        $ticket = SupportTicket::sole();
        $this->assertSame($this->user->id, $ticket->user_id);
        $this->assertSame(SupportStatus::Open, $ticket->status);
        $this->assertSame('Nahrávanie videa', $ticket->subject);
        $this->assertSame(1, $ticket->messages()->count());

        Notification::assertSentTo($this->admin, SupportTicketCreated::class);
        Notification::assertNotSentTo($this->user, SupportTicketCreated::class);
    }

    public function test_pata_posiela_spravu_do_podpory(): void
    {
        $form = 'action="'.route('profile.support.store').'"';

        $this->get(route('gdpr'))->assertOk()->assertDontSee($form, false);

        $this->actingAs($this->user)->get(route('gdpr'))->assertOk()->assertSee($form, false);
    }

    public function test_nebezpecna_adresa_stranky_neprejde(): void
    {
        $this->actingAs($this->user)
            ->post(route('profile.support.store'), [
                'subject' => 'Otázka',
                'body' => 'Dobrý deň',
                'page_url' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('page_url');
    }

    public function test_nova_sprava_aj_odpoved_sa_objavia_v_zvoncku(): void
    {
        $this->actingAs($this->user)->post(route('profile.support.store'), [
            'subject' => 'Zvonček',
            'body' => 'Prídem do zvončeka?',
        ]);
        $ticket = SupportTicket::sole();

        $this->actingAs($this->admin)->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('meta.unread', 1)
            ->assertJsonPath('data.0.data.message', 'Podpora #'.str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT).': Zvonček')
            ->assertJsonPath('data.0.data.link', route('admin.support.show', $ticket));

        $this->actingAs($this->admin)->post(route('admin.support.reply', $ticket), ['body' => 'Áno.']);

        $this->actingAs($this->user)->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('meta.unread', 1)
            ->assertJsonPath('data.0.data.link', route('profile.support.show', $ticket));

        // Otvorenie vlákna upozornenie v zvončeku prečíta.
        $this->actingAs($this->user)->get(route('profile.support.show', $ticket));
        $this->assertSame(0, $this->user->unreadNotifications()->count());
    }

    public function test_predmet_je_povinny(): void
    {
        $this->actingAs($this->user)
            ->post(route('profile.support.store'), ['body' => 'Dobrý deň'])
            ->assertSessionHasErrors('subject');

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_odpoved_spravcu_dostane_pouzivatel_a_vidi_ju_v_nastenke(): void
    {
        Notification::fake();
        $ticket = $this->ticket();

        $this->actingAs($this->admin)
            ->post(route('admin.support.reply', $ticket), ['body' => 'Skúste to znova, prosím.'])
            ->assertRedirect(route('admin.support.show', $ticket));

        $ticket->refresh();
        $this->assertSame(SupportStatus::Answered, $ticket->status);
        $this->assertNull($ticket->user_seen_at);
        $this->assertTrue($ticket->messages->last()->is_staff);
        Notification::assertSentTo($this->user, SupportTicketReplied::class);

        // Používateľ vidí podporu, nie meno konkrétneho správcu.
        $this->actingAs($this->user)->get(route('profile.support.show', $ticket))
            ->assertOk()
            ->assertSee('Skúste to znova, prosím.')
            ->assertSee('Podpora HlasCirkvi.sk')
            ->assertDontSee($this->admin->email);

        $this->assertNotNull($ticket->refresh()->user_seen_at);
    }

    public function test_doplnenie_pouzivatela_vlakno_znovu_otvori(): void
    {
        Notification::fake();
        $ticket = $this->ticket();
        $ticket->update(['status' => SupportStatus::Closed]);

        $this->actingAs($this->user)
            ->post(route('profile.support.reply', $ticket), ['body' => 'Ešte jedna vec.'])
            ->assertRedirect();

        $this->assertSame(SupportStatus::Open, $ticket->refresh()->status);
        $this->assertNull($ticket->staff_seen_at);
        Notification::assertSentTo($this->admin, SupportTicketReplied::class);
    }

    public function test_cudzie_vlakno_je_neviditelne(): void
    {
        $ticket = $this->ticket();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('profile.support.show', $ticket))->assertNotFound();
        $this->actingAs($intruder)->post(route('profile.support.reply', $ticket), ['body' => 'x'])->assertNotFound();
        $this->actingAs($intruder)->get(route('profile.support.index'))->assertOk()->assertDontSee($ticket->subject);
    }

    public function test_administraciu_podpory_vidi_len_spravca(): void
    {
        $ticket = $this->ticket();

        $this->actingAs($this->user)->get(route('admin.support.index'))->assertRedirect('/');

        $this->actingAs($this->admin)->get(route('admin.support.index'))
            ->assertOk()->assertSee($ticket->subject);
        $this->actingAs($this->admin)->get(route('admin.support.show', $ticket))
            ->assertOk()->assertSee($this->user->email)->assertSee('Prvá správa');
    }

    public function test_pouzivatel_vlakno_uzavrie(): void
    {
        $ticket = $this->ticket();

        $this->actingAs($this->user)
            ->put(route('profile.support.status', $ticket), ['status' => 'closed'])
            ->assertRedirect();

        $this->assertSame(SupportStatus::Closed, $ticket->refresh()->status);
    }

    private function ticket(): SupportTicket
    {
        Notification::fake();

        $this->actingAs($this->user)->post(route('profile.support.store'), [
            'subject' => 'Otázka k účtu',
            'body' => 'Prvá správa',
        ]);

        return SupportTicket::sole();
    }
}
