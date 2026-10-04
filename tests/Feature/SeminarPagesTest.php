<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\Seminar;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeminarPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function seminar(): array
    {
        $owner = User::factory()->create();
        $canal = Canal::factory()->create();
        $owner->canals()->attach($canal);
        $seminar = Seminar::create([
            'canal_id' => $canal->id, 'title' => 'Prednášky seminára',
            'published' => now(), 'youtube_playlist' => 'PL12345678901234567890123456789012',
        ]);
        $post = Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Viditeľná prednáška']);
        $seminar->posts()->attach($post);

        return [$owner, $canal, $seminar, $post];
    }

    public function test_public_page_renders_cards_and_hides_management_from_other_users(): void
    {
        [$owner, $canal, $seminar, $post] = $this->seminar();
        $hidden = Post::factory()->unpublished()->create(['canal_id' => $canal->id, 'title' => 'Skrytá prednáška']);
        $seminar->posts()->attach($hidden);
        session()->forget('flash');
        $this->get(route('seminars.show', $seminar))->assertOk()
            ->assertSee('Viditeľná prednáška')->assertDontSee('Skrytá prednáška')
            ->assertDontSee('Spravovať seminár')->assertDontSee('<card-front', false);
        $this->actingAs(User::factory()->create())->get(route('seminars.show', $seminar))->assertOk()
            ->assertDontSee('Spravovať seminár')->assertDontSee('Načítať videá z YouTube')
            ->assertDontSee('Zrušiť zverejnenie');
    }

    public function test_owner_sees_working_management_links_and_csrf_protected_import(): void
    {
        [$owner, $canal, $seminar, $post] = $this->seminar();
        $this->actingAs($owner)->get(route('profile.canals.seminars.show', [$canal, $seminar]))
            ->assertOk()->assertSee('Viditeľná prednáška')->assertDontSee('<card-front', false)
            ->assertSee(route('profile.canals.seminars.edit', [$canal, $seminar]), false)
            ->assertSee(route('profile.canals.seminars.destroy', [$canal, $seminar]), false)
            ->assertSee('action="'.route('seminars.uploadVideos', $seminar).'" method="post"', false)
            ->assertSee('name="_token"', false);
        $this->get(route('profile.canals.seminars.edit', [$canal, $seminar]))->assertOk();
        $this->put(route('profile.canals.seminars.update', [$canal, $seminar]), ['published' => ''])
            ->assertRedirect(route('profile.canals.seminars.index', $canal));
        $this->assertNull($seminar->fresh()->published);
    }

    public function test_legacy_seminar_post_url_resolves_only_posts_in_the_seminar(): void
    {
        [$owner, $canal, $seminar, $post] = $this->seminar();
        $this->get(route('seminars.posts.show', [$seminar, $post]))
            ->assertRedirect(route('post.show', [$post->id, $post->slug]));
        $unrelated = Post::factory()->create(['canal_id' => $canal->id]);
        $this->get(route('seminars.posts.show', [$seminar, $unrelated]))->assertNotFound();
    }

    public function test_other_user_cannot_manage_seminar_or_import_playlist(): void
    {
        [$owner, $canal, $seminar] = $this->seminar();
        $this->actingAs(User::factory()->create());
        $this->get(route('profile.canals.seminars.show', [$canal, $seminar]))->assertForbidden();
        $this->post(route('seminars.uploadVideos', $seminar))->assertForbidden();
        $this->put(route('profile.canals.seminars.update', [$canal, $seminar]), ['published' => now()->toDateTimeString()])
            ->assertForbidden();
    }
    public function test_unpublished_seminar_is_private_but_owner_can_preview_it(): void
    {
        [$owner, $canal, $seminar] = $this->seminar();
        $seminar->update(['published' => null]);
        $this->get(route('seminars.show', $seminar))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('seminars.show', $seminar))->assertNotFound();
        $this->actingAs($owner)->get(route('seminars.show', $seminar))->assertOk();
    }

    public function test_education_preview_limits_each_series_and_keeps_full_counts(): void
    {
        [$owner, $canal, $seminar, $post] = $this->seminar();
        $extra = Post::factory()->count(5)->create(['canal_id' => $canal->id]);
        $seminar->posts()->attach($extra);
        $hidden = Post::factory()->unpublished()->create(['canal_id' => $canal->id]);
        $seminar->posts()->attach($hidden);
        $other = Seminar::create(['title' => 'Druhá séria', 'canal_id' => $canal->id, 'published' => now()]);
        $other->posts()->attach($extra);
        session()->forget('flash');

        $this->get(route('konferencie.pute'))->assertOk()
            ->assertSee('Zobraziť všetky prednášky (6)')
            ->assertSee('Zobraziť všetky prednášky (5)')
            ->assertViewHas('seminars', fn ($seminars) => $seminars->count() === 2
                && $seminars->every(fn ($row) => $row->posts->count() === 4)
                && $seminars->sum('posts_count') === 11);
        $this->get(route('seminars.show', $seminar))->assertOk()
            ->assertViewHas('seminar', fn ($row) => $row->posts->count() === 6);
    }
    public function test_filters_use_event_year_not_import_year_and_search_public_lectures(): void
    {
        [$owner, $canal, $seminar, $post] = $this->seminar();
        $seminar->update(['title' => 'Hanusove dni 2023', 'created_at' => '2026-10-04 12:00:00']);
        $post->update(['title' => 'Nádej a viera']);
        $hidden = Post::factory()->unpublished()->create(['canal_id' => $canal->id, 'title' => 'Tajná téma']);
        $seminar->posts()->attach($hidden);
        $other = Seminar::create(['title' => 'Hanusove dni 2024', 'canal_id' => $canal->id, 'published' => now()]);
        $unknown = Seminar::create(['title' => 'Večerná univerzita', 'canal_id' => $canal->id, 'published' => now()]);
        Seminar::create(['title' => 'Súkromný kurz 2025', 'canal_id' => $canal->id]);
        session()->forget('flash');

        $this->get(route('konferencie.pute', ['q' => 'Nádej', 'year' => '2023']))->assertOk()
            ->assertSee('Nádej a viera')->assertSee('value="Nádej"', false)
            ->assertViewHas('year', '2023')
            ->assertViewHas('years', fn ($years) => $years->all() === [2024, 2023])
            ->assertViewHas('seminars', fn ($rows) => $rows->modelKeys() === [$seminar->id]);
        $this->get(route('konferencie.pute', ['year' => '2026']))->assertOk()
            ->assertViewHas('seminars', fn ($rows) => $rows->isEmpty())->assertSee('Zrušiť filtre');
        $this->get(route('konferencie.pute', ['q' => 'Tajná téma']))->assertOk()
            ->assertViewHas('seminars', fn ($rows) => $rows->isEmpty());
        $this->get(route('konferencie.pute', ['year' => 'unknown']))->assertOk()
            ->assertViewHas('seminars', fn ($rows) => $rows->modelKeys() === [$unknown->id]);
        $this->get(route('konferencie.pute', ['q' => $canal->title, 'year' => '2024']))->assertOk()
            ->assertViewHas('seminars', fn ($rows) => $rows->modelKeys() === [$other->id]);
        $this->get(route('konferencie.pute', ['q' => '%']))->assertOk()
            ->assertViewHas('seminars', fn ($rows) => $rows->isEmpty());
    }
    public function test_admin_search_and_publication_filter_keep_each_other(): void
    {
        [, $canal, $seminar] = $this->seminar();
        $seminar->update(['title' => 'Kontrolná séria']);
        $hidden = Seminar::create(['title' => 'Kontrolná súkromná séria', 'canal_id' => $canal->id]);
        Seminar::create(['title' => 'Iná séria', 'canal_id' => $canal->id, 'published' => now()]);
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);

        $this->actingAs($admin)
            ->get(route('admin.seminar.index', ['search' => 'Kontrolná', 'status' => 'published']))
            ->assertOk()
            ->assertViewHas('seminars', fn ($rows) => $rows->modelKeys() === [$seminar->id])
            ->assertSee('name="status" value="published"', false)
            ->assertSee('name="search" value="Kontrolná"', false);

        $this->get(route('admin.seminar.index', ['search' => 'Kontrolná', 'status' => 'unpublished']))
            ->assertOk()
            ->assertViewHas('seminars', fn ($rows) => $rows->modelKeys() === [$hidden->id]);
    }
}