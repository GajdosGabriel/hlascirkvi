<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\Seminar;
use App\Models\User;
use App\Services\VideoUploadSeminars;
use App\Services\Youtube\YoutubeApi;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VideoCollectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    private function owner(): array
    {
        $canal = Canal::factory()->create();
        $user = User::factory()->create(['canal_id' => $canal->id]);
        $user->canals()->attach($canal);
        $this->actingAs($user);
        return [$user, $canal];
    }

    private function collection(Canal $canal, array $extra = []): Seminar
    {
        return Seminar::create(array_merge(['title' => 'Biblické prednášky', 'kind' => 'collection', 'canal_id' => $canal->id], $extra));
    }

    private function data(array $extra = []): array
    {
        return array_merge(['title' => 'Nový príspevok', 'body' => 'Text príspevku', 'section' => 'front', 'publish_now' => '1'], $extra);
    }

    public function test_creation_without_any_collection_and_multiple_optional_memberships(): void
    {
        [, $canal] = $this->owner();
        $this->post(route('profile.posts.store'), $this->data())->assertRedirect()->assertSessionHasNoErrors();
        $post = $canal->posts()->latest('id')->firstOrFail();
        $this->assertCount(0, $post->seminars);
        $a = $this->collection($canal);
        $b = $this->collection($canal, ['title' => 'Druhá kolekcia']);
        $this->put(route('profile.posts.update', $post), $this->data(['collections' => [$a->id, $b->id], 'collections_present' => '1']))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $post->fresh()->seminars->modelKeys());
        $this->put(route('profile.posts.update', $post), $this->data())->assertSessionHasNoErrors();
        $this->assertCount(2, $post->fresh()->seminars);
        $this->put(route('profile.posts.update', $post), $this->data(['collections_present' => '1']))->assertSessionHasNoErrors();
        $this->assertCount(0, $post->fresh()->seminars);
        $this->assertSame('front', $post->fresh()->section->value);
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_foreign_and_deleted_collections_cannot_be_assigned(): void
    {
        [, $canal] = $this->owner();
        $post = Post::factory()->create(['canal_id' => $canal->id]);
        $foreign = $this->collection(Canal::factory()->create());
        $deleted = $this->collection($canal);
        $deleted->delete();
        foreach ([$foreign, $deleted] as $collection) {
            $this->put(route('profile.posts.update', $post), $this->data(['collections' => [$collection->id]]))
                ->assertSessionHasErrors('collections.0');
        }
        $this->assertCount(0, $post->fresh()->seminars);
    }

    public function test_collection_form_and_creation_accept_playlist_url_and_optional_fields(): void
    {
        [, $canal] = $this->owner();
        $this->get(route('profile.canals.seminars.create', $canal))->assertOk()->assertSee('Tematická kolekcia');
        $playlist = 'PL12345678901234567890123456789012';
        $this->post(route('profile.canals.seminars.store', $canal), [
            'title' => 'Moja kolekcia', 'kind' => 'collection', 'youtube_playlist' => 'https://www.youtube.com/playlist?list='.$playlist,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $collection = $canal->seminars()->firstOrFail();
        $this->assertSame('collection', $collection->kind);
        $this->assertSame($playlist, $collection->youtube_playlist);
        $this->assertNull($collection->published);
        $this->get(route('profile.canals.seminars.show', [$canal, $collection]))->assertOk()->assertSee('Pridať')->assertSee('Zverejniť');
    }

    public function test_bulk_add_and_remove_preserve_other_collections_and_posts(): void
    {
        [, $canal] = $this->owner();
        $a = $this->collection($canal);
        $b = $this->collection($canal);
        $posts = Post::factory()->count(2)->create(['canal_id' => $canal->id]);
        $b->posts()->attach($posts);
        $url = route('profile.canals.seminars.posts', [$canal, $a]);
        foreach ([1, 2] as $attempt) {
            $this->post($url, ['action' => 'add', 'posts' => $posts->modelKeys()])->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->assertSame(2, $a->posts()->count());
        $this->post($url, ['action' => 'remove', 'posts' => [$posts[0]->id]])->assertSessionHasNoErrors();
        $this->assertSame(1, $a->posts()->count());
        $this->assertSame(2, $b->posts()->count());
        $this->assertNotNull($posts[0]->fresh());
        $this->delete(route('profile.canals.seminars.destroy', [$canal, $a]))->assertRedirect();
        $this->assertNotNull($posts[1]->fresh());
        $this->assertSame(2, $b->posts()->count());
    }

    public function test_bulk_mutations_enforce_channel_ownership_and_parent_binding(): void
    {
        [, $canal] = $this->owner();
        $collection = $this->collection($canal);
        $foreign = Canal::factory()->create();
        $post = Post::factory()->create(['canal_id' => $foreign->id]);
        $url = route('profile.canals.seminars.posts', [$canal, $collection]);
        $this->post($url, ['action' => 'add', 'posts' => [$post->id]])->assertSessionHasErrors('posts.0');
        $this->post(route('profile.canals.seminars.posts', [$foreign, $collection]), ['action' => 'add', 'posts' => [$post->id]])->assertNotFound();
        $this->actingAs(User::factory()->create())->post($url, ['action' => 'add', 'posts' => [$post->id]])->assertForbidden();
        $this->assertSame(0, $collection->posts()->count());
    }

    public function test_management_filters_and_post_form_show_only_permitted_collections(): void
    {
        [, $canal] = $this->owner();
        $collection = $this->collection($canal);
        $this->collection(Canal::factory()->create(), ['title' => 'Cudzia tajná kolekcia']);
        $member = Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Vybraná prednáška']);
        Post::factory()->create(['canal_id' => $canal->id, 'title' => 'Iný príspevok']);
        $collection->posts()->attach($member);
        $this->get(route('profile.canals.seminars.show', [$canal, $collection, 'membership' => 'in', 'q' => 'Vybraná']))
            ->assertOk()->assertViewHas('posts', fn ($posts) => $posts->modelKeys() === [$member->id]);
        $this->get(route('profile.posts.edit', $member))->assertOk()->assertSee('<collection-select', false)
            ->assertSee(json_encode('Biblické prednášky'), false)->assertDontSee(json_encode('Cudzia tajná kolekcia'), false);
    }

    public function test_public_collections_are_on_channel_and_post_but_only_seminars_in_event_archive(): void
    {
        $canal = Canal::factory()->create();
        $collection = $this->collection($canal, ['published' => now()]);
        $seminar = $this->collection($canal, ['kind' => 'seminar', 'title' => 'Konferencia 2026', 'published' => now()]);
        $draft = $this->collection($canal, ['title' => 'Skrytá kolekcia']);
        $post = Post::factory()->create(['canal_id' => $canal->id]);
        foreach ([$collection, $seminar, $draft] as $item) $item->posts()->attach($post);
        $this->get(route('organizations.show', $canal))->assertOk()->assertSee('Biblické prednášky')->assertSee('Konferencia 2026')->assertDontSee('Skrytá kolekcia');
        $this->get(route('post.show', [$post->id, $post->slug]))->assertOk()->assertSee('Biblické prednášky')->assertDontSee('Skrytá kolekcia');
        $this->get(route('konferencie.pute'))->assertOk()->assertSee('Konferencia 2026')->assertDontSee('Biblické prednášky');
        $this->get(route('seminars.show', $draft))->assertNotFound();
    }

    public function test_public_collection_hides_unpublished_unavailable_and_deleted_posts_and_paginates(): void
    {
        $canal = Canal::factory()->create();
        $collection = $this->collection($canal, ['published' => now()]);
        $posts = Post::factory()->count(25)->create(['canal_id' => $canal->id]);
        $draft = Post::factory()->unpublished()->create(['canal_id' => $canal->id]);
        $unavailable = Post::factory()->create(['canal_id' => $canal->id, 'video_available' => false]);
        $deleted = Post::factory()->create(['canal_id' => $canal->id]);
        $collection->posts()->attach([...$posts->modelKeys(), $draft->id, $unavailable->id, $deleted->id]);
        $deleted->delete();
        $this->get(route('seminars.show', $collection))->assertOk()
            ->assertViewHas('posts', fn ($result) => $result->total() === 25 && $result->count() === 24);
        $this->get(route('seminars.show', [$collection, 'page' => 2]))->assertOk()
            ->assertViewHas('posts', fn ($result) => $result->modelKeys() === [$posts->last()->id]);
    }

    public function test_playlist_reimport_preserves_memberships_and_never_restores_deleted_videos(): void
    {
        $canal = Canal::factory()->create();
        $collection = $this->collection($canal, ['youtube_playlist' => 'PL12345678901234567890123456789012']);
        $other = $this->collection($canal);
        $post = Post::factory()->create(['canal_id' => $canal->id, 'video_id' => 'abcdefghijk']);
        $deleted = Post::factory()->create(['canal_id' => $canal->id, 'video_id' => 'lmnopqrstuv']);
        $foreign = Post::factory()->create(['video_id' => 'xyz01234567']);
        $deleted->delete();
        $other->posts()->attach($post);
        $api = Mockery::mock(YoutubeApi::class);
        $api->shouldReceive('playlistItems')->twice()->andReturn((object) [
            'items' => array_map(fn ($id) => (object) ['contentDetails' => (object) ['videoId' => $id]], [$post->video_id, $deleted->video_id, $foreign->video_id]),
            'nextPageToken' => null,
        ]);
        $service = new VideoUploadSeminars($collection, $canal, $api);
        $service->handle();
        $service->handle();
        $this->assertEqualsCanonicalizing([$collection->id, $other->id], $post->fresh()->seminars->modelKeys());
        $this->assertEqualsCanonicalizing([$post->id, $foreign->id], $collection->posts()->pluck('posts.id')->all());
        $this->assertSoftDeleted($deleted);
        $this->assertSame('front', $post->fresh()->section->value);
    }

    public function test_legacy_cross_channel_membership_survives_post_edit_and_can_be_removed_by_collection_owner(): void
    {
        [$owner, $canal] = $this->owner();
        $collection = $this->collection($canal);
        $foreign = Canal::factory()->create();
        $post = Post::factory()->create(['canal_id' => $foreign->id]);
        $collection->posts()->attach($post);
        $owner->canals()->attach($foreign);
        $this->put(route('profile.posts.update', $post), $this->data(['collections_present' => '1']))->assertSessionHasNoErrors();
        $this->assertSame([$collection->id], $post->fresh()->seminars->modelKeys());
        $this->get(route('profile.canals.seminars.show', [$canal, $collection, 'membership' => 'in']))
            ->assertOk()->assertViewHas('posts', fn ($rows) => $rows->modelKeys() === [$post->id]);
        $this->post(route('profile.canals.seminars.posts', [$canal, $collection]), ['action' => 'remove', 'posts' => [$post->id]])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertCount(0, $post->fresh()->seminars);
    }

    public function test_channel_move_requires_new_channels_collections_and_removes_old_memberships(): void
    {
        [$owner, $canal] = $this->owner();
        $other = Canal::factory()->create();
        $owner->canals()->attach($other);
        $post = Post::factory()->create(['canal_id' => $canal->id]);
        $original = $this->collection($canal);
        $target = $this->collection($other);
        $post->seminars()->attach($original);
        $this->put(route('profile.posts.update', $post), $this->data(['canal_id' => $other->id, 'collections' => [$original->id]]))
            ->assertSessionHasErrors('collections.0');
        $this->assertSame($canal->id, $post->fresh()->canal_id);
        $this->put(route('profile.posts.update', $post), $this->data(['canal_id' => $other->id, 'collections' => [$target->id]]))
            ->assertSessionHasNoErrors();
        $this->assertSame([$target->id], $post->fresh()->seminars->modelKeys());
    }

    public function test_collection_on_hidden_channel_is_not_public(): void
    {
        $canal = Canal::factory()->unpublished()->create();
        $collection = $this->collection($canal, ['published' => now()]);
        $this->get(route('seminars.show', $collection))->assertNotFound();
        $this->assertFalse(Seminar::published()->whereKey($collection->id)->exists());
    }

}
