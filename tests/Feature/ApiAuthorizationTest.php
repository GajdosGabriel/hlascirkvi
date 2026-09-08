<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Image;
use App\Models\Organization;
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
     * napevno village_id 4209 a nie je v pivote organization_user, takže si
     * vlastníctvo doplníme explicitne.
     */
    protected function userWithOrganization(): array
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();

        $user->organizations()->attach($organization);
        $user->update(['org_id' => $organization->id]);

        return [$user->fresh(), $organization];
    }

    // ---------------------------------------------------------------- hosť

    public function test_host_nesmie_menit_ani_mazat_cez_api(): void
    {
        [$owner, $organization] = $this->userWithOrganization();

        $post = Post::factory()->create(['organization_id' => $organization->id]);
        $comment = Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'user_id' => $owner->id,
        ]);
        $prayer = Prayer::factory()->create(['organization_id' => $organization->id]);

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
        [, $organization] = $this->userWithOrganization();

        $post = Post::factory()->create(['organization_id' => $organization->id]);
        Prayer::factory()->create(['organization_id' => $organization->id]);

        $this->getJson('/api/comments')->assertOk();
        $this->getJson('/api/posts')->assertOk();
        $this->getJson('/api/prayers')->assertOk();
        $this->getJson('/api/prayers/fulfilled')->assertOk();
        $this->getJson("/api/posts/{$post->id}/comments")->assertOk();
        $this->getJson("/api/organization/{$organization->id}")->assertOk();
    }

    public function test_anonymny_komentar_je_stale_mozny(): void
    {
        [, $organization] = $this->userWithOrganization();
        $post = Post::factory()->create(['organization_id' => $organization->id]);

        $this->postJson("/api/posts/{$post->id}/comments", [
            'body' => 'Komentár od neprihláseného návštevníka.',
            'email' => 'navstevnik@example.com',
        ])->assertSuccessful();

        $this->assertDatabaseHas('comments', ['commentable_id' => $post->id]);
        // Formulár si podľa e-mailu založí účet a prihlási ho.
        $this->assertDatabaseHas('users', ['email' => 'navstevnik@example.com']);
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
        [$owner, $organization] = $this->userWithOrganization();
        [$intruder] = $this->userWithOrganization();

        $post = Post::factory()->create(['organization_id' => $organization->id]);
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
        $this->putJson("/user/{$owner->id}/organization/{$organization->id}", [
            'title' => 'Prepísaný kanál',
            'village_id' => $organization->village_id,
        ])->assertForbidden();

        $this->assertDatabaseHas('organizations', ['id' => $organization->id, 'title' => $organization->title]);
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('images', ['id' => $image->id, 'deleted_at' => null]);
    }

    public function test_zverejnenie_prispevku_je_len_pre_superadmina(): void
    {
        [$owner, $organization] = $this->userWithOrganization();
        $post = Post::factory()->create(['organization_id' => $organization->id]);

        // CheckSuperAdmin nevracia 403, ale presmeruje na úvodnú stránku.
        $this->actingAs($owner)
            ->put("/api/posts/{$post->id}", ['youtube_blocked' => 1])
            ->assertRedirect('/');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'youtube_blocked' => 0]);
    }

    // ---------------------------------------------------------- vlastník

    public function test_vlastnik_smie_so_svojim_komentarom_aj_modlitbou(): void
    {
        [$owner, $organization] = $this->userWithOrganization();

        $post = Post::factory()->create(['organization_id' => $organization->id]);
        $comment = Comment::factory()->create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'user_id' => $owner->id,
        ]);
        $prayer = Prayer::factory()->create(['organization_id' => $organization->id]);

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
        [$owner, $organization] = $this->userWithOrganization();

        $this->actingAs($owner)
            ->put("/user/{$owner->id}/organization/{$organization->id}", [
                'title' => 'Nový názov kanála',
                'village_id' => $organization->village_id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'title' => 'Nový názov kanála',
        ]);
    }

    public function test_published_a_spravcov_kanala_smie_menit_len_superadmin(): void
    {
        [$owner, $organization] = $this->userWithOrganization();
        [$cudzi] = $this->userWithOrganization();

        $organization->update(['published' => 1]);

        $this->actingAs($owner)
            ->put("/user/{$owner->id}/organization/{$organization->id}", [
                'title' => $organization->title,
                'village_id' => $organization->village_id,
                'published' => 0,
                'users' => [$cudzi->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('organizations', ['id' => $organization->id, 'published' => 1]);
        $this->assertDatabaseMissing('organization_user', [
            'organization_id' => $organization->id,
            'user_id' => $cudzi->id,
        ]);
    }

    // ------------------------------------------------- zmazané debug routy

    public function test_debug_endpointy_uz_neexistuju(): void
    {
        $this->getJson('/api/artisan/run')->assertNotFound();
        $this->getJson('/api/test/test')->assertNotFound();
        $this->getJson('/api/test/grecky')->assertNotFound();
        $this->get('/openAi')->assertNotFound();
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
