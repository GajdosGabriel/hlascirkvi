<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\User;
use App\Support\MediaUrl;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CanalAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        config(['images.disk' => 'public', 'images.remote_base' => null]);
        Storage::fake('public');
    }

    private function manager(Canal $canal): User
    {
        $user = User::factory()->create();
        $canal->users()->attach($user);
        return $user;
    }

    private function payload(Canal $canal, array $changes = []): array
    {
        return array_merge(['title' => $canal->title, 'village_id' => $canal->village_id], $changes);
    }

    public function test_edit_form_offers_avatar_upload_and_current_photo(): void
    {
        $canal = Canal::factory()->create(['avatar' => 'old.jpg']);
        $this->actingAs($this->manager($canal))
            ->get("/dashboard/canals/{$canal->id}/edit")
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="avatar_file"', false)
            ->assertSee(MediaUrl::canalAvatar($canal->id, 'old.jpg'), false)
            ->assertSee('name="remove_avatar"', false);
    }

    public function test_manager_can_upload_replace_and_remove_avatar_on_image_disk(): void
    {
        $canal = Canal::factory()->create();
        $this->actingAs($this->manager($canal));

        $this->post("/dashboard/canals/{$canal->id}", $this->payload($canal, [
            '_method' => 'PUT', 'avatar_file' => UploadedFile::fake()->image('portrait.png', 1200, 800),
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $first = $canal->fresh()->avatar;
        Storage::disk('public')->assertExists("organizations/{$canal->id}/{$first}");
        $dimensions = getimagesizefromstring(Storage::disk('public')->get("organizations/{$canal->id}/{$first}"));
        $this->assertLessThanOrEqual(512, max($dimensions[0], $dimensions[1]));

        $this->put("/dashboard/canals/{$canal->id}", $this->payload($canal))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($first, $canal->fresh()->avatar);

        $this->post("/dashboard/canals/{$canal->id}", $this->payload($canal, [
            '_method' => 'PUT', 'remove_avatar' => '1',
            'avatar_file' => UploadedFile::fake()->image('replacement.jpg'),
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $second = $canal->fresh()->avatar;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing("organizations/{$canal->id}/{$first}");
        Storage::disk('public')->assertExists("organizations/{$canal->id}/{$second}");

        $this->put("/dashboard/canals/{$canal->id}", $this->payload($canal, ['remove_avatar' => '1']))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($canal->fresh()->avatar);
        Storage::disk('public')->assertMissing("organizations/{$canal->id}/{$second}");
    }

    public function test_invalid_upload_keeps_existing_avatar(): void
    {
        $canal = Canal::factory()->create(['avatar' => 'old.jpg']);
        Storage::disk('public')->put("organizations/{$canal->id}/old.jpg", 'old');
        $this->actingAs($this->manager($canal));

        foreach ([
            UploadedFile::fake()->create('document.pdf', 20, 'application/pdf'),
            UploadedFile::fake()->image('oversized.jpg')->size(5121),
            UploadedFile::fake()->image('wide.png', 6001, 1),
        ] as $file) {
            $this->put("/dashboard/canals/{$canal->id}", $this->payload($canal, ['avatar_file' => $file]))
                ->assertSessionHasErrors('avatar_file');
            $this->assertSame('old.jpg', $canal->fresh()->avatar);
        }
        Storage::disk('public')->assertExists("organizations/{$canal->id}/old.jpg");
    }

    public function test_storage_failure_preserves_previous_avatar_and_channel_data(): void
    {
        $canal = Canal::factory()->create(['avatar' => 'old.jpg']);
        $this->actingAs($this->manager($canal));
        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('put')->once()->andReturn(false);
        $disk->shouldReceive('delete')->once()->andReturn(true);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $this->put("/dashboard/canals/{$canal->id}", $this->payload($canal, [
            'title' => 'New title', 'avatar_file' => UploadedFile::fake()->image('photo.jpg'),
        ]))->assertSessionHasErrors('avatar_file');
        $this->assertSame('old.jpg', $canal->fresh()->avatar);
        $this->assertSame($canal->title, $canal->fresh()->title);
    }
    public function test_other_user_cannot_replace_avatar(): void
    {
        $canal = Canal::factory()->create(['avatar' => 'old.jpg']);
        $this->actingAs(User::factory()->create())
            ->put("/dashboard/canals/{$canal->id}", $this->payload($canal, [
                'avatar_file' => UploadedFile::fake()->image('photo.jpg'),
            ]))->assertForbidden();
        $this->assertSame('old.jpg', $canal->fresh()->avatar);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}