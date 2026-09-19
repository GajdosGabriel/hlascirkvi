<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Image;
use App\Models\Canal;
use App\Models\Post;
use App\Models\Prayer;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sieť na zachytenie regresií po bezpečnostnom hotfixe.
 *
 * Celý blok apiResources v routes/api.php stál mimo auth:sanctum — mazanie
 * komentárov, editácia príspevkov a mazanie modlitieb boli dostupné anonymne.
 * Zároveň sú tri zápisy verejné zámerne (anonymný komentár, modlitba a
 * označenie kanála ako obľúbeného), takže testy strážia obe strany: že sa
 * chránené endpointy nedajú volať bez prihlásenia a že tie verejné nezmizli.
 */
class ApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bez rolí sa nedá založiť ani jeden užívateľ — UserObserver::created
        // volá assignRole('user') a scope role('admin').
        $this->seed(RolesSeeder::class);
    }

    /**
     * Užívateľ so svojím kanálom. UserObserver mu jeden založí sám, ale ten má
     * napevno village_id 4209 a nie je v pivote canal_user, takže si
     * vlastníctvo doplníme explicitne.
     */
    protected function userWithCanal(): array
    {
        $user = User::factory()->create();
        $canal = Canal::factory()->create();

        $user->canals()->attach($canal);
        $user->update(['canal_id' => $canal->id]);

        return [$user->fresh(), $canal];
    }

    // ---------------------------------------------------------------- hosť

    public function test_host_nesmie_menit_ani_mazat_cez_api(): void
    {
        [$owner, $canal] = $this->userWithCanal();

        $post = Post::factory()->create(['canal_id' => $canal->id]);
        $comment = Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'user_id' => $owner->id,
        ]);
        $prayer = Prayer::factory()->create(['canal_id' => $canal->id]);

        $this->deleteJson("/api/comments/{$comment->id}")->assertUnauthorized();
        $this->putJson("/api/posts/{$post->id}", ['youtube_blocked' => 1])->assertUnauthorized();
        $this->putJson("/api/postSupport/{$post->id}")->assertUnauthorized();
        $this->putJson("/api/prayers/{$prayer->id}", ['title' => 'Nový', 'body' => 'Nový text'])->assertUnauthorized();
        $this->deleteJson("/api/prayers/{$prayer->id}")->assertUnauthorized();
        $this->putJson("/api/posts/{$post->id}/comments/{$comment->id}", ['body' => 'prepis'])->assertUnauthorized();
        $this->deleteJson("/api/posts/{$post->id}/comments/{$comment->id}")->assertUnauthorized();
        $this->putJson("/api/users/{$owner->id}", ['notify_bell' => now()->toDateTimeString()])->assertUnauthorized();

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('prayers', ['id' => $prayer->id, 'deleted_at' => null]);
    }

    public function test_verejne_citanie_ostava_dostupne_bez_prihlasenia(): void
    {
        [, $canal] = $this->userWithCanal();

        $post = Post::factory()->create(['canal_id' => $canal->id]);
        Prayer::factory()->create(['canal_id' => $canal->id]);

        $this->getJson('/api/comments')->assertOk();
        $this->getJson('/api/posts')->assertOk();
        $this->getJson('/api/prayers')->assertOk();
        $this->getJson('/api/prayers/fulfilled')->assertOk();
        $this->getJson("/api/posts/{$post->id}/comments")->assertOk();
        $this->getJson("/api/organization/{$canal->id}")->assertOk();
    }

    public function test_anonymny_komentar_je_stale_mozny(): void
    {
        [, $canal] = $this->userWithCanal();
        $post = Post::factory()->create(['canal_id' => $canal->id]);

        $this->postJson("/api/posts/{$post->id}/comments", [
            'body' => 'Komentár od neprihláseného návštevníka.',
            'email' => 'navstevnik@example.com',
        ])->assertSuccessful();

        $this->assertDatabaseHas('comments', ['commentable_id' => $post->id]);
        // Formulár si podľa e-mailu založí účet a prihlási ho.
        $this->assertDatabaseHas('users', ['email' => 'navstevnik@example.com']);
    }

    public function test_anonymny_formular_neprihlasi_existujuci_ucet_podla_emailu(): void
    {
        [$victim, $canal] = $this->userWithCanal();
        $post = Post::factory()->create(['canal_id' => $canal->id]);

        $this->postJson('/api/posts/'.$post->getKey().'/comments', [
            'body' => 'Pokus o komentár pod cudzím účtom.',
            'email' => $victim->getAttribute('email'),
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertGuest();
        $this->assertDatabaseMissing('comments', [
            'commentable_id' => $post->getKey(),
            'body' => 'Pokus o komentár pod cudzím účtom.',
        ]);
    }

    public function test_anonymna_modlitba_je_stale_mozna(): void
    {
        $this->postJson('/api/prayers', [
            'title' => 'Prosba o zdravie',
            'body' => 'Modlite sa prosím za moju rodinu.',
            'user_name' => 'Anonym',
            'email' => 'modlitba@example.com',
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'modlitba@example.com']);
    }

    // ------------------------------------------------------- cudzí užívateľ

    public function test_prihlaseny_nesmie_zasahovat_do_cudzich_zaznamov(): void
    {
        [$owner, $canal] = $this->userWithCanal();
        [$intruder] = $this->userWithCanal();

        $post = Post::factory()->create(['canal_id' => $canal->id]);
        $comment = Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'user_id' => $owner->id,
        ]);
        $image = Image::factory()->create([
            'fileable_id' => $post->id,
            'fileable_type' => Post::class,
        ]);

        $this->actingAs($intruder);

        $this->putJson("/api/users/{$owner->id}", ['notify_bell' => now()->toDateTimeString()])->assertForbidden();
        $this->deleteJson("/api/comments/{$comment->id}")->assertForbidden();
        $this->deleteJson("/images/{$image->id}")->assertForbidden();
        $this->putJson("/dashboard/canals/{$canal->id}", [
            'title' => 'Prepísaný kanál',
            'village_id' => $canal->village_id,
        ])->assertForbidden();

        $this->assertDatabaseHas('canals', ['id' => $canal->id, 'title' => $canal->title]);
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('images', ['id' => $image->id, 'deleted_at' => null]);
    }

    public function test_zverejnenie_prispevku_je_len_pre_superadmina(): void
    {
        [$owner, $canal] = $this->userWithCanal();
        $post = Post::factory()->create(['canal_id' => $canal->id]);

        // CheckSuperAdmin nevracia 403, ale presmeruje na úvodnú stránku.
        $this->actingAs($owner)
            ->put("/api/posts/{$post->id}", ['youtube_blocked' => 1])
            ->assertRedirect('/');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'youtube_blocked' => 0]);
    }

    // ---------------------------------------------------------- vlastník

    public function test_vlastnik_smie_so_svojim_komentarom_aj_modlitbou(): void
    {
        [$owner, $canal] = $this->userWithCanal();

        $post = Post::factory()->create(['canal_id' => $canal->id]);
        $comment = Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'user_id' => $owner->id,
        ]);
        $prayer = Prayer::factory()->create(['canal_id' => $canal->id]);

        $this->actingAs($owner);

        $this->putJson("/api/posts/{$post->id}/comments/{$comment->id}", ['body' => 'Upravený komentár'])
            ->assertOk();
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'body' => 'Upravený komentár']);

        // PrayerController::update mal parameter $modlitby, hoci routa nesie
        // {prayer} — väzba sa nenaviazala a úprava ticho nerobila nič.
        $this->putJson("/api/prayers/{$prayer->id}", ['title' => 'Upravená', 'body' => 'Upravený text'])
            ->assertSuccessful();
        $this->assertDatabaseHas('prayers', ['id' => $prayer->id, 'title' => 'Upravená']);

        $this->deleteJson("/api/comments/{$comment->id}")->assertSuccessful();
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_vlastnik_smie_upravit_svoj_kanal(): void
    {
        [$owner, $canal] = $this->userWithCanal();

        $this->actingAs($owner)
            ->put("/dashboard/canals/{$canal->id}", [
                'title' => 'Nový názov kanála',
                'village_id' => $canal->village_id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('canals', [
            'id' => $canal->id,
            'title' => 'Nový názov kanála',
        ]);
    }

    public function test_published_a_spravcov_kanala_smie_menit_len_superadmin(): void
    {
        [$owner, $canal] = $this->userWithCanal();
        [$cudzi] = $this->userWithCanal();

        $canal->update(['published' => 1]);

        $this->actingAs($owner)
            ->put("/dashboard/canals/{$canal->id}", [
                'title' => $canal->title,
                'village_id' => $canal->village_id,
                'published' => 0,
                'users' => [$cudzi->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('canals', ['id' => $canal->id, 'published' => 1]);
        $this->assertDatabaseMissing('canal_user', [
            'canal_id' => $canal->id,
            'user_id' => $cudzi->id,
        ]);
    }

    /**
     * Kanály pridané príkazom youtube:channels nemajú správcu. Superadmin
     * ich musí vedieť uložiť bez toho, aby niekoho do správcov dosadil,
     * a prázdny výber musí správcov aj reálne odobrať.
     */
    public function test_superadmin_ulozi_kanal_bez_spravcu(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole(['admin', 'superadmin']);

        $canal = Canal::factory()->create();

        $this->actingAs($superadmin)
            ->put("/dashboard/canals/{$canal->id}", [
                'title' => 'Kanál bez správcu',
                'village_id' => $canal->village_id,
                'users_submitted' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('canals', ['id' => $canal->id, 'title' => 'Kanál bez správcu']);

        [$owner, $vlastny] = $this->userWithCanal();

        $this->actingAs($superadmin)
            ->put("/dashboard/canals/{$vlastny->id}", [
                'title' => $vlastny->title,
                'village_id' => $vlastny->village_id,
                'users_submitted' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('canal_user', ['canal_id' => $vlastny->id]);
    }

    public function test_odobraty_spravca_strati_pristup_aj_cez_aktivny_kanal(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole(['admin', 'superadmin']);

        [$owner, $canal] = $this->userWithCanal();
        // Osobný kanál z UserObservera — ten mu po odobratí ostane.
        $druhy = $owner->canals()->whereKeyNot($canal->id)->firstOrFail();

        $this->actingAs($superadmin)
            ->put("/dashboard/canals/{$canal->id}", [
                'title' => $canal->title,
                'village_id' => $canal->village_id,
                'users_submitted' => 1,
            ])
            ->assertRedirect();

        // Aktívny kanál sa prepne na iný kanál, ktorý ešte spravuje.
        $this->assertSame($druhy->id, $owner->fresh()->canal_id);

        $this->actingAs($owner->fresh())
            ->get("/dashboard/canals/{$canal->id}/edit")
            ->assertForbidden();
    }

    public function test_cudzi_aktivny_kanal_nedava_pristup(): void
    {
        [$owner, $canal] = $this->userWithCanal();
        $cudzi = User::factory()->create();
        $cudzi->update(['canal_id' => $canal->id]);

        $this->actingAs($cudzi->fresh())
            ->get("/dashboard/canals/{$canal->id}/edit")
            ->assertForbidden();
    }

    public function test_modlitbu_do_kanala_pridava_len_jeho_spravca(): void
    {
        [$owner, $canal] = $this->userWithCanal();
        [$cudzi] = $this->userWithCanal();

        $prayer = ['title' => 'Za zdravie', 'body' => 'Prosím o modlitbu.'];

        $this->actingAs($cudzi)->get("/dashboard/canals/{$canal->id}/prayers/create")->assertForbidden();
        $this->actingAs($cudzi)->post("/dashboard/canals/{$canal->id}/prayers", $prayer)->assertForbidden();

        // Prezývka je nepovinná.
        $this->actingAs($owner)->post("/dashboard/canals/{$canal->id}/prayers", $prayer)->assertRedirect();
        $this->assertSame(1, $canal->prayers()->count());
    }

    public function test_odkaz_v_modlitbe_smie_len_spravca_v_nastenke(): void
    {
        [$owner, $canal] = $this->userWithCanal();
        $prayer = ['title' => 'Za zdravie', 'body' => 'Viac na https://www.example.sk'];

        $this->actingAs($owner)->post("/dashboard/canals/{$canal->id}/prayers", $prayer)
            ->assertSessionHasNoErrors();

        // Verejné API ostáva bez odkazov aj pre prihláseného.
        $this->actingAs($owner)->postJson('/api/prayers', $prayer)
            ->assertJsonValidationErrors('body');
    }

    public function test_nastenka_sa_zobrazi_aj_bez_aktivneho_kanala(): void
    {
        $user = User::factory()->create();
        $user->update(['canal_id' => null]);

        $this->actingAs($user->fresh())
            ->get('/dashboard/canals')
            ->assertOk();
    }

    // ------------------------------------------------- zmazané debug routy

    public function test_debug_endpointy_uz_neexistuju(): void
    {
        $this->getJson('/api/artisan/run')->assertNotFound();
        $this->getJson('/api/test/test')->assertNotFound();
        $this->getJson('/api/test/grecky')->assertNotFound();
        $this->get('/openAi')->assertNotFound();
    }

    public function test_nepodporovane_resource_akcie_uz_router_neregistruje(): void
    {
        $this->get('/favorites')->assertNotFound();
        $this->postJson('/api/users')->assertNotFound();
        $this->deleteJson('/api/villages/1')->assertMethodNotAllowed();
    }

    public function test_youtube_import_je_post_a_len_pre_superadmina(): void
    {
        [$user] = $this->userWithCanal();
        $url = '/youtube/user/'.$user->getKey().'/test/search';

        $this->get($url)->assertMethodNotAllowed();

        // Middleware zastaví bežného používateľa skôr, než sa spustí externé
        // vyhľadávanie a zápis videí.
        $this->actingAs($user)->post($url)->assertRedirect('/');
    }

    public function test_prihlasovacie_routy_existuju(): void
    {
        // Auth::routes() bolo z routes/web.php odstránené a /login prestalo
        // existovať, hoci naň posiela resources/js/auth/LoginForm.vue.
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/password/reset')->assertOk();
    }
}
