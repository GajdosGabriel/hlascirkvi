<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Zvonček v navigácii vedel notifikáciu iba označiť za prečítanú. Ostávali
 * v ňom preto naveky aj desiatky rovnakých hlásení (jeden spamový modlitebný
 * úmysel), ktoré sa nedali zmazať. Testy strážia nové akcie aj to, že sa
 * užívateľ nedostane k cudzím notifikáciám.
 */
class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    private function notification(User $user, ?string $readAt = null, string $message = 'Správa'): string
    {
        $id = (string) Str::uuid();

        $user->notifications()->create([
            'id' => $id,
            'type' => 'App\Notifications\Prayer\NewPrayer',
            'data' => ['message' => $message, 'link' => '/modlitby', 'logo' => 'J'],
            'read_at' => $readAt,
        ]);

        return $id;
    }

    public function test_hostovi_nie_su_notifikacie_dostupne(): void
    {
        $user = User::factory()->create();
        $id = $this->notification($user);

        $this->getJson('/api/notifications')->assertUnauthorized();
        $this->deleteJson("/api/notifications/{$id}")->assertUnauthorized();
        $this->postJson('/api/notifications/read')->assertUnauthorized();
        $this->deleteJson('/api/notifications')->assertUnauthorized();

        $this->assertDatabaseHas('notifications', ['id' => $id]);
    }

    public function test_vypis_vracia_vlastne_notifikacie_a_pocet_neprecitanych(): void
    {
        $user = User::factory()->create();
        $intruder = User::factory()->create();

        $this->notification($user);
        $this->notification($user, now()->toDateTimeString());
        $this->notification($intruder, null, 'Cudzia správa');

        $response = $this->actingAs($user)->getJson('/api/notifications')->assertOk();

        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('meta.unread', 1);
        $response->assertJsonMissing(['message' => 'Cudzia správa']);
    }

    public function test_notifikaciu_sa_da_oznacit_precitanu_aj_neprecitanu(): void
    {
        $user = User::factory()->create();
        $id = $this->notification($user);

        $this->actingAs($user)->putJson("/api/notifications/{$id}")->assertOk();
        $this->assertNotNull($user->notifications()->find($id)->read_at);

        $this->actingAs($user)->putJson("/api/notifications/{$id}", ['read' => false])->assertOk();
        $this->assertNull($user->notifications()->find($id)->read_at);
    }

    public function test_hromadne_oznacenie_precitanych(): void
    {
        $user = User::factory()->create();
        $first = $this->notification($user);
        $second = $this->notification($user);

        $this->actingAs($user)->postJson('/api/notifications/read', ['ids' => [$first]])->assertNoContent();

        $this->assertNotNull($user->notifications()->find($first)->read_at);
        $this->assertNull($user->notifications()->find($second)->read_at);

        $this->actingAs($user)->postJson('/api/notifications/read')->assertNoContent();
        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_mazanie_jednej_skupiny_aj_vsetkeho(): void
    {
        $user = User::factory()->create();
        $spam = collect(range(1, 3))->map(fn () => $this->notification($user, null, 'Spam'))->all();
        $keep = $this->notification($user, null, 'Dôležité');

        $this->actingAs($user)
            ->deleteJson('/api/notifications', ['ids' => $spam])
            ->assertNoContent();

        $this->assertSame(1, $user->notifications()->count());
        $this->assertDatabaseHas('notifications', ['id' => $keep]);

        $this->actingAs($user)->deleteJson("/api/notifications/{$keep}")->assertNoContent();
        $this->assertSame(0, $user->notifications()->count());
    }

    public function test_mazanie_len_precitanych_necha_neprecitane(): void
    {
        $user = User::factory()->create();
        $read = $this->notification($user, now()->toDateTimeString());
        $unread = $this->notification($user);

        $this->actingAs($user)->deleteJson('/api/notifications', ['only' => 'read'])->assertNoContent();

        $this->assertDatabaseMissing('notifications', ['id' => $read]);
        $this->assertDatabaseHas('notifications', ['id' => $unread]);
    }

    public function test_cudziu_notifikaciu_nezmaze_ani_neprecita(): void
    {
        $user = User::factory()->create();
        $intruder = User::factory()->create();
        $id = $this->notification($user);

        $this->actingAs($intruder);

        $this->deleteJson("/api/notifications/{$id}")->assertNotFound();
        $this->putJson("/api/notifications/{$id}")->assertNotFound();
        $this->deleteJson('/api/notifications', ['ids' => [$id]])->assertNoContent();
        $this->postJson('/api/notifications/read', ['ids' => [$id]])->assertNoContent();

        $this->assertDatabaseHas('notifications', ['id' => $id, 'read_at' => null]);
    }
}
