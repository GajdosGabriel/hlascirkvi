<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Image;
use App\Models\Post;
use App\Models\Prayer;
use App\Models\Seminar;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * App\Console\Commands\CanalsDedupe: zjavná duplicita je rovnaký základ
 * názvu (bez „(2)“) pod tým istým správcom — samotná zhoda mena (menovci
 * s rôznymi účtami) sa zlučovať nesmie.
 */
class CanalsDedupeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // UserObserver::created volá assignRole('user').
        $this->seed(RolesSeeder::class);
    }

    public function test_duplicitu_pod_tym_istym_spravcom_zlepi_do_zivsieho_kanala(): void
    {
        $manager = User::factory()->create();

        $keeper = Canal::factory()->create(['title' => 'Ján Novák']);
        $loser = Canal::factory()->create(['title' => 'Ján Novák (2)']);

        $keeper->users()->attach($manager->id);
        $loser->users()->attach($manager->id);

        // Keeper musí mať aspoň toľko obsahu ako duplikát (posts+prayers+seminars),
        // inak by si "menej obsiahnutý" duplikát podľa CanalsDedupe ponechal on.
        Post::factory()->count(3)->create(['canal_id' => $keeper->id]);
        $post = Post::factory()->create(['canal_id' => $loser->id]);
        $prayer = Prayer::factory()->create(['canal_id' => $loser->id]);
        $seminar = Seminar::create(['title' => 'Seminár', 'canal_id' => $loser->id]);
        $comment = Comment::factory()->create(['commentable_id' => $loser->id, 'commentable_type' => Canal::class]);
        $image = Image::factory()->create(['fileable_id' => $loser->id, 'fileable_type' => Canal::class]);
        $favorite = Favorite::create(['user_id' => $manager->id, 'favorited_id' => $loser->id, 'favorited_type' => Canal::class]);

        $this->artisan('canals:dedupe', ['--fix' => true])->assertSuccessful();

        $this->assertSoftDeleted('canals', ['id' => $loser->id]);
        $this->assertNotSoftDeleted('canals', ['id' => $keeper->id]);

        $this->assertSame($keeper->id, $post->fresh()->canal_id);
        $this->assertSame($keeper->id, $prayer->fresh()->canal_id);
        $this->assertSame($keeper->id, $seminar->fresh()->canal_id);
        $this->assertSame($keeper->id, $comment->fresh()->commentable_id);
        $this->assertSame($keeper->id, $image->fresh()->fileable_id);
        $this->assertSame($keeper->id, $favorite->fresh()->favorited_id);

        $this->assertTrue($keeper->users()->whereKey($manager->id)->exists());
        $this->assertSame(1, $keeper->users()->count());
    }

    public function test_rovnaky_nazov_pod_inym_spravcom_sa_nezluci(): void
    {
        Canal::factory()->create(['title' => 'Erika Banduričová'])
            ->users()->attach(User::factory()->create()->id);

        $other = Canal::factory()->create(['title' => 'Erika Banduričová (2)']);
        $other->users()->attach(User::factory()->create()->id);

        $this->artisan('canals:dedupe')
            ->expectsOutput('Žiadne zjavné duplicity nenájdené.')
            ->assertSuccessful();

        $this->assertNotSoftDeleted('canals', ['id' => $other->id]);
    }

    public function test_duplicitny_oblubeny_kanal_sa_zahodi_nie_zdvoji(): void
    {
        $manager = User::factory()->create();

        $keeper = Canal::factory()->create(['title' => 'Ján Novák']);
        $loser = Canal::factory()->create(['title' => 'Ján Novák (2)']);

        $keeper->users()->attach($manager->id);
        $loser->users()->attach($manager->id);

        $fan = User::factory()->create();
        Favorite::create(['user_id' => $fan->id, 'favorited_id' => $keeper->id, 'favorited_type' => Canal::class]);
        Favorite::create(['user_id' => $fan->id, 'favorited_id' => $loser->id, 'favorited_type' => Canal::class]);

        $this->artisan('canals:dedupe', ['--fix' => true])->assertSuccessful();

        $this->assertSame(1, Favorite::where('favorited_type', Canal::class)->where('favorited_id', $keeper->id)->count());
    }

    public function test_prazdne_polia_keepera_sa_doplnia_z_duplikatu(): void
    {
        $manager = User::factory()->create();

        $keeper = Canal::factory()->create(['title' => 'Ján Novák', 'description' => null]);
        $loser = Canal::factory()->create(['title' => 'Ján Novák (2)', 'description' => 'Kazateľ v Trenčíne']);

        $keeper->users()->attach($manager->id);
        $loser->users()->attach($manager->id);

        $this->artisan('canals:dedupe', ['--fix' => true])->assertSuccessful();

        $this->assertSame('Kazateľ v Trenčíne', $keeper->fresh()->description);
    }
}
