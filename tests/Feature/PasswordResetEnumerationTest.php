<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetEnumerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_obnova_hesla_neprezradi_ci_ucet_existuje(): void
    {
        User::factory()->create(['email' => 'jan.novak@gmail.com']);

        $known = $this->from('/password/reset')->post('/password/email', ['email' => 'jan.novak@gmail.com']);
        $unknown = $this->from('/password/reset')->post('/password/email', ['email' => 'nikto@example.com']);

        $unknown->assertSessionHasNoErrors();
        $this->assertSame(session('status'), trans('passwords.sent'));
        $this->assertSame($known->getStatusCode(), $unknown->getStatusCode());
    }
}
