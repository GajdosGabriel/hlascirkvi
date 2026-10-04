<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCanalSelectTest extends TestCase
{
    use RefreshDatabase;

    public function test_channel_search_receives_only_managed_channels_and_keeps_the_posts_channel(): void
    {
        $this->seed(RolesSeeder::class);
        $active = Canal::factory()->create(['title' => 'Aktívny kanál']);
        $owner = User::factory()->create(['canal_id' => $active->id]);
        $owner->assignRole('admin');
        $edited = Canal::factory()->create(['title' => 'Kanál článku']);
        $owner->canals()->attach([$active->id, $edited->id]);
        Canal::factory()->create(['title' => 'Cudzí kanál iba pre superadmina']);
        $post = Post::factory()->create(['canal_id' => $edited->id]);
        $response = $this->actingAs($owner)->get(route('profile.posts.edit', $post))->assertOk()
            ->assertSee('<canal-select', false)
            ->assertSee(":selected='\"".$edited->id."\"'", false);
        preg_match("/:canals='([^']+)'/", $response->getContent(), $matches);
        $options = json_decode(html_entity_decode($matches[1]), true, flags: JSON_THROW_ON_ERROR);
        $this->assertEqualsCanonicalizing([$active->id, $edited->id], array_column($options, 'id'));
    }
}
