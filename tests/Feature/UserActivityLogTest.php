<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Prayer;
use App\Models\SystemLog;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Denník má pri účte ukázať, čo človek na webe robil — nielen prihlásenie.
 * Uloženie príspevku na neskôr pokrýva SavedPostTest.
 */
class UserActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
        $this->user = User::factory()->create();
    }

    public function test_oznacenie_a_jeho_zrusenie(): void
    {
        $prayer = Prayer::factory()->create(['title' => 'Za pokoj']);
        $payload = ['model' => 'Prayer', 'model_id' => $prayer->id];

        $this->actingAs($this->user)->putJson("/favorites/{$prayer->id}", $payload + ['favorited' => true])->assertOk();
        // Opakované označenie nič nemení, takže sa ani nezapíše.
        $this->actingAs($this->user)->putJson("/favorites/{$prayer->id}", $payload + ['favorited' => true])->assertOk();
        $this->actingAs($this->user)->putJson("/favorites/{$prayer->id}", $payload)->assertOk();

        $this->assertSame(
            ['favorite.added' => 'Označené (modlitba): Za pokoj', 'favorite.removed' => 'Označenie zrušené (modlitba): Za pokoj'],
            $this->logs('favorite')->pluck('message', 'event')->all(),
        );
        $this->assertSame(2, $this->logs('favorite')->count());
        $this->assertSame($prayer->id, (int) $this->logs('favorite')->first()->subject_id);
    }

    public function test_komentar_a_jeho_zmazanie(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($this->user)
            ->postJson("/api/posts/{$post->id}/comments", ['body' => 'Ďakujem za povzbudenie.'])
            ->assertSuccessful();

        $created = $this->logs('comment')->sole();
        $this->assertSame('comment.created', $created->event);
        $this->assertSame('Komentár: Ďakujem za povzbudenie.', $created->message);

        $this->actingAs($this->user)
            ->deleteJson("/api/posts/{$post->id}/comments/{$created->subject_id}")
            ->assertSuccessful();

        $this->assertSame(['comment.created', 'comment.deleted'], $this->logs('comment')->pluck('event')->all());
    }

    public function test_sprava_sa_zapise_bez_textu(): void
    {
        // Správy z formulára v päte chodia správcovi s pevným ID 1
        // (MessengerController::ADMIN_ID); v samostatnom behu ho má $this->user.
        User::find(1) ?? User::factory()->create(['id' => 1]);

        $this->actingAs($this->user)
            ->postJson(route('messengers.store'), ['body' => 'Tajný text správy'])
            ->assertOk();

        $log = $this->logs('message')->sole();
        $this->assertSame('message.sent', $log->event);
        $this->assertStringNotContainsString('Tajný', json_encode($log->getAttributes(), JSON_UNESCAPED_UNICODE));
    }

    public function test_odhlasenie(): void
    {
        $this->actingAs($this->user)->post(route('logout'));

        $this->assertDatabaseHas('system_logs', ['event' => 'auth.logout', 'user_id' => $this->user->id]);
    }

    private function logs(string $channel)
    {
        return SystemLog::where('channel', $channel)->where('user_id', $this->user->id)->orderBy('id')->get();
    }
}
