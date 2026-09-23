<?php

namespace Tests\Feature;

use App\Enums\CanalType;
use App\Models\User;
use App\Notifications\User\NewRegistration;
use Database\Seeders\RolesSeeder;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Kanál a správa adminom až pre overenú adresu (App\Services\UserActivation).
 */
class UserActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    protected function unverifiedUser(): User
    {
        $user = new User(['first_name' => 'Ján', 'last_name' => 'Novák', 'email' => 'jan.novak@gmail.com', 'password' => 'x']);
        $user->save();

        return $user->fresh();
    }

    public function test_neovereny_ucet_nema_kanal_ani_spravu_adminom(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Notification::fake();

        $user = $this->unverifiedUser();

        $this->assertNull($user->canal_id);
        $this->assertSame(0, $user->canals()->count());
        Notification::assertNotSentTo($admin, NewRegistration::class);
    }

    public function test_overenie_zalozi_kanal_a_upovedomi_adminov(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Notification::fake();

        $user = $this->unverifiedUser();
        $user->markEmailAsVerified();
        event(new Verified($user));

        $this->assertNotNull($user->fresh()->canal_id);
        $this->assertSame(1, $user->canals()->count());
        $this->assertSame(CanalType::Personal, $user->canals()->first()->type);
        Notification::assertSentTo($admin, NewRegistration::class);

        // Opakované Verified nič nezdvojí.
        event(new Verified($user));
        $this->assertSame(1, $user->canals()->count());
        Notification::assertSentTimes(NewRegistration::class, 1);
    }

    public function test_obnova_hesla_overi_adresu_a_aktivuje_ucet(): void
    {
        $user = $this->unverifiedUser();

        event(new PasswordReset($user));

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame(1, $user->canals()->count());
    }

    public function test_admin_vidi_namiesto_maskovaneho_mena_cast_emailu(): void
    {
        $user = new User(['first_name' => 'K•••m', 'last_name' => '', 'email' => 'krajcikova.martina.km@gmail.com']);

        $this->assertTrue($user->hasPlaceholderName());
        $this->assertSame('krajcikova.martina.km (meno nezadané)', $user->adminName());

        $named = new User(['first_name' => 'Martina', 'last_name' => 'Krajčíková', 'email' => 'krajcikova.martina.km@gmail.com']);

        $this->assertFalse($named->hasPlaceholderName());
        $this->assertSame('Krajčíková Martina', $named->adminName());
    }

    public function test_overeny_ucet_ma_kanal_hned(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->canal_id);
        $this->assertSame(1, $user->canals()->count());
    }
}
