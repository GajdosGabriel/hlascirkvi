<?php

namespace Tests\Feature;

use App\Models\SystemLog;
use App\Models\User;
use App\Notifications\Admin\BufeerIsEmpty;
use App\Notifications\User\ResetPassword;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemLogMailBodyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
    }

    public function test_sent_mail_keeps_its_body_and_detail_shows_it(): void
    {
        $this->admin->notifyNow(new BufeerIsEmpty);

        $log = SystemLog::where('event', 'mail.sent')->sole();
        $this->assertStringContainsString('Otvoriť zásobník', $log->body);
        $this->assertSame(BufeerIsEmpty::class, $log->context['class']);
        $this->assertNotEmpty($log->context['from']);
        $this->assertArrayNotHasKey('body', $log->toArray());

        $this->actingAs($this->admin)->get(route('admin.logs.index'))
            ->assertOk()->assertSee(route('admin.logs.show', $log->id), false);

        $this->actingAs($this->admin)->get(route('admin.logs.show', $log->id))
            ->assertOk()->assertSee('Prázdny zásobník videí')->assertSee($this->admin->email)
            ->assertSee('sandbox="allow-popups allow-popups-to-escape-sandbox"', false)
            ->assertSee(e('<base target="_blank">' . $log->body), false);
    }

    public function test_mail_with_token_link_is_logged_without_body(): void
    {
        $this->admin->notifyNow(new ResetPassword('tajny-token'));

        $log = SystemLog::where('event', 'mail.sent')->sole();
        $this->assertNull($log->body);
        $this->assertSame('sensitive', $log->context['body_omitted']);

        $this->actingAs($this->admin)->get(route('admin.logs.show', $log->id))
            ->assertOk()->assertSee('Obsah tohto e-mailu sa neukladá')->assertDontSee('tajny-token');
    }

    public function test_detail_is_only_for_mail_events_and_superadmin(): void
    {
        $login = SystemLog::create(['channel' => 'auth', 'event' => 'auth.login', 'message' => 'Prihlásenie']);
        $mail = SystemLog::create(['channel' => 'mail', 'event' => 'mail.sent', 'message' => 'Starý e-mail']);

        $this->actingAs($this->admin)->get(route('admin.logs.show', $login->id))->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.logs.show', $mail->id))
            ->assertOk()->assertSee('Obsah tohto e-mailu nie je uložený');

        $response = $this->actingAs(User::factory()->create())->get(route('admin.logs.show', $mail->id));
        $this->assertNotSame(200, $response->getStatusCode());
    }
}
