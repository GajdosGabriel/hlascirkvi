<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Výpis komentárov v administrácii: pri komentári je článok, pod ktorým
 * visí, jeho kanál a súvislosti (odpoveď na koho, koľko odpovedí).
 */
class AdminCommentListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole(['admin', 'superadmin']);
    }

    public function test_komentar_odkazuje_na_clanok_a_kanal(): void
    {
        $canal = Canal::factory()->create(['title' => 'Farnosť Komentárová']);
        $post = Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Homília na nedeľu', 'slug' => 'homilia-na-nedelu']);
        $parent = Comment::factory()->create(['commentable_id' => $post->id, 'body' => 'Pôvodná myšlienka', 'user_name' => 'Mária']);
        Comment::factory()->create(['commentable_id' => $post->id, 'parent_id' => $parent->id, 'body' => 'Súhlasím s vami', 'user_name' => 'Jozef']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.comment.index'))
            ->assertOk()
            ->assertSee('Homília na nedeľu')
            ->assertSee(route('post.show', [$post->id, 'homilia-na-nedelu']) . '#komentare', false)
            ->assertSee('Farnosť Komentárová')
            ->assertSee('na Mária:')
            ->assertSee('2 v diskusii')
            ->assertSee('1 odpoveď')
            ->assertSee('Najživšie diskusie za 7 dní')
            ->assertDontSee('<comment-item', false);

        if (getenv('DUMP_PREVIEW')) {
            file_put_contents(public_path('_preview_comments.html'), $response->getContent());
        }
    }

    public function test_home_sa_vykresli_s_prehladom(): void
    {
        $canal = Canal::factory()->create();
        $post = Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Najčítanejší článok']);
        Comment::factory()->create(['commentable_id' => $post->id, 'body' => 'Krásne slová']);
        \Illuminate\Support\Facades\DB::table('views')->insert([
            'viewable_type' => Post::class,
            'viewable_id' => $post->id,
            'visitor_hash' => str_repeat('a', 64),
            'viewed_on' => now()->toDateString(),
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/home')
            ->assertOk()
            ->assertSee('Kedy sa číta')
            ->assertSee('Najčítanejší článok')
            ->assertSee('Kanály, ktoré ťahajú')
            ->assertSee('Krásne slová');

        if (getenv('DUMP_PREVIEW')) {
            file_put_contents(public_path('_preview_home_test.html'), $response->getContent());
        }
    }
}
