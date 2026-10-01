<?php

namespace Tests\Feature;

use App\Mail\PostNewsletter;
use App\Models\Messenger;
use App\Models\Post;
use App\Models\Prayer;
use App\Models\User;
use App\Notifications\Messengers\ComingNewMessage;
use App\Notifications\Prayer\PrayerFulfilledOrNotYet;
use App\Notifications\User\ConfirmEmail;
use App\Notifications\User\NewRegistration;
use App\Notifications\User\ResetPassword;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Jednotná podoba e-mailov (App\Notifications\Messages\PortalMail, téma
 * hlascirkvi) a odhlásenie z newslettera.
 */
class MailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    protected function assertPortalLayout(string $html): void
    {
        $this->assertStringContainsString('Hlas Cirkvi', $html);
        $this->assertStringContainsString('tím HlasCirkvi.sk', $html);
        $this->assertStringNotContainsString('Laravel', $html);
        $this->assertStringNotContainsString('All rights reserved', $html);
        $this->assertStringNotContainsString('trouble clicking', $html);
    }

    public function test_obnova_hesla_je_po_slovensky(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $html = (string) $notification->toMail($user)->render();

            $this->assertPortalLayout($html);
            $this->assertStringContainsString('Nastaviť nové heslo', $html);
            $this->assertStringNotContainsString('Reset Password', $html);

            return true;
        });
    }

    public function test_overovaci_email_ma_sablonu_portalu(): void
    {
        $user = User::factory()->create();

        $html = (string) (new ConfirmEmail($user))->toMail($user)->render();

        $this->assertPortalLayout($html);
        $this->assertStringContainsString('Potvrdiť e-mailovú adresu', $html);
        $this->assertStringContainsString('Ak tlačidlo', $html);
    }

    public function test_udaje_od_pouzivatelov_sa_escapuju(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['first_name' => '<b>Ján</b>']);

        $html = (string) (new NewRegistration($user))->toMail($admin)->render();

        $this->assertStringNotContainsString('<b>Ján</b>', $html);
        $this->assertStringContainsString('&lt;b&gt;', $html);
        $this->assertStringContainsString('class="details"', $html);
    }

    public function test_sprava_od_pouzivatela_ide_s_reply_to(): void
    {
        $sender = User::factory()->create(['email' => 'odosielatel@example.com']);
        $recipient = User::factory()->create();

        $message = new Messenger(['body' => "Prvý riadok\nDruhý riadok"]);
        $message->setRelation('senderUser', $sender);

        $mail = (new ComingNewMessage($message))->toMail($recipient);
        $html = (string) $mail->render();

        $this->assertSame([['odosielatel@example.com', $sender->fullname]], $mail->replyTo);
        $this->assertStringContainsString('Prvý riadok<br>Druhý riadok', $html);
    }

    public function test_otazka_na_vypocutu_modlitbu(): void
    {
        $prayer = Prayer::factory()->create();
        $owner = User::factory()->create();

        $html = (string) (new PrayerFulfilledOrNotYet($prayer))->toMail($owner)->render();

        $this->assertPortalLayout($html);
        $this->assertStringContainsString('Áno, modlitba bola vypočutá', $html);
        $this->assertStringContainsString('button-success', $html);
    }

    public function test_newsletter_ma_odkaz_na_odhlasenie(): void
    {
        $user = User::factory()->create();
        $mail = new PostNewsletter(
            Post::factory()->count(2)->create(),
            Prayer::factory()->count(2)->create(),
            $user,
        );

        $html = $mail->render();

        $this->assertStringContainsString('Najsledovanejšie videá', $html);
        $this->assertStringContainsString('Nové modlitby', $html);
        $this->assertStringContainsString('Odhlásiť odber', $html);
        // Markdown nesmie HTML bloky rozbiť na text.
        $this->assertStringNotContainsString('&lt;table', $html);
        $this->assertStringContainsString('List-Unsubscribe', json_encode($mail->headers()->text));
    }

    public function test_odhlasenie_z_newslettera(): void
    {
        $user = User::factory()->create(['send_email' => true]);
        $url = URL::signedRoute('newsletter.unsubscribe', ['user' => $user->id]);

        $this->get($url)->assertOk()->assertSee('Áno, odhlásiť odber');
        $this->assertTrue($user->fresh()->send_email, 'GET nesmie odhlásiť — odkazy otvárajú aj poštové skenery.');

        $this->post($url)->assertOk()->assertSee('Odber je zrušený');
        $this->assertFalse($user->fresh()->send_email);
    }

    public function test_hlavicka_ma_jednoklikove_odhlasenie(): void
    {
        $mail = new PostNewsletter(collect(), collect(), User::factory()->create());

        $this->assertSame('List-Unsubscribe=One-Click', $mail->headers()->text['List-Unsubscribe-Post']);
    }

    public function test_jednoklikove_odhlasenie_bez_csrf_zapise_datum_a_ip(): void
    {
        $user = User::factory()->create(['send_email' => true]);
        $url = URL::signedRoute('newsletter.unsubscribe', ['user' => $user->id]);

        // Poštový server posiela telo bez tokenu; CSRF sa v testoch inak vypína.
        $this->withMiddleware()->post($url, ['List-Unsubscribe' => 'One-Click'])->assertOk();

        $user->refresh();
        $this->assertFalse($user->send_email);
        $this->assertNotNull($user->newsletter_unsubscribed_at);
        $this->assertNotNull($user->newsletter_unsubscribed_ip);

        // Opakovanie dátum neprepíše.
        $at = $user->newsletter_unsubscribed_at;
        $this->travel(1)->days();
        $this->post($url)->assertOk()->assertSee('Odber je zrušený');
        $this->assertTrue($at->equalTo($user->fresh()->newsletter_unsubscribed_at));
    }

    public function test_odber_sa_da_obnovit_z_odkazu_aj_z_profilu(): void
    {
        $user = User::factory()->create(['send_email' => false, 'newsletter_unsubscribed_at' => now()]);
        $url = URL::signedRoute('newsletter.resubscribe', ['user' => $user->id]);

        $this->get(URL::signedRoute('newsletter.unsubscribe', ['user' => $user->id]))
            ->assertSee('Zrušiť, chcem odber');

        $this->post($url)->assertOk();
        $this->assertTrue($user->fresh()->send_email);
        $this->assertNull($user->fresh()->newsletter_unsubscribed_at);

        $this->actingAs($user->fresh())->put('/odber-noviniek', ['send_email' => 0])->assertRedirect();
        $this->assertFalse($user->fresh()->send_email);
        $this->actingAs($user->fresh())->put('/odber-noviniek', ['send_email' => 1])->assertRedirect();
        $this->assertTrue($user->fresh()->send_email);
    }

    public function test_newsletter_sa_za_mesiac_rozosle_len_raz(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        User::factory()->create(['send_email' => true]);

        $this->assertTrue((new \App\Services\Newsletter)->mountlyNewsletter());
        $this->assertFalse((new \App\Services\Newsletter)->mountlyNewsletter());
        \Illuminate\Support\Facades\Mail::assertQueuedCount(1);

        $this->assertTrue((new \App\Services\Newsletter)->mountlyNewsletter(force: true));
        \Illuminate\Support\Facades\Mail::assertQueuedCount(2);
    }

    public function test_odhlasenie_bez_podpisu_neprejde(): void
    {
        $user = User::factory()->create(['send_email' => true]);

        $this->post('/newsletter/odhlasit/'.$user->id)->assertForbidden();
        $this->assertTrue($user->fresh()->send_email);
    }
}
