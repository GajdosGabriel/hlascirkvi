<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\User;
use App\Services\FrontList\FrontList;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CanalVideoSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function actor(Canal $canal, string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $canal->users()->attach($user);

        return $user->fresh();
    }

    private function payload(Canal $canal, array $changes): array
    {
        return $changes + ['title' => $canal->title, 'village_id' => $canal->village_id];
    }

    public function test_superadmin_can_add_and_remove_front_list_membership_and_clear_cache(): void
    {
        $canal = Canal::factory()->create();
        $this->actingAs($this->actor($canal, 'superadmin'));
        foreach ([1, 0] as $listed) {
            Cache::put(FrontList::CACHE_KEY, ['cached'], 60);
            $this->put('/dashboard/canals/'.$canal->id, $this->payload($canal, ['front_listed' => $listed]))
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame((bool) $listed, $canal->fresh()->front_listed_at !== null);
            $this->assertNull(Cache::get(FrontList::CACHE_KEY));
        }
    }

    public function test_other_roles_cannot_change_front_list_by_forging_field(): void
    {
        foreach (['user', 'admin'] as $role) {
            $canal = Canal::factory()->create();
            $this->actingAs($this->actor($canal, $role))
                ->put('/dashboard/canals/'.$canal->id, $this->payload($canal, ['front_listed' => 1]))
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->assertNull($canal->fresh()->front_listed_at);
        }
    }

    public function test_automatic_day_is_saved_and_rescheduling_clears_old_cursor(): void
    {
        foreach (range(1, 6) as $day) {
            Canal::factory()->create(['import_day' => $day]);
        }
        $canal = Canal::factory()->create([
            'youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw', 'import_day' => 2,
            'name_search_page_token' => 'obsolete', 'video_check_error' => 'old error',
        ]);
        $this->actingAs($this->actor($canal, 'admin'))
            ->put('/dashboard/canals/'.$canal->id, $this->payload($canal, ['import_day' => 'auto']))
            ->assertSessionHasNoErrors()->assertRedirect();
        $canal->refresh();
        $this->assertSame(0, $canal->import_day);
        $this->assertSame(0, $canal->video_check_next_at->dayOfWeek);
        $this->assertSame('16:24', $canal->video_check_next_at->format('H:i'));
        $this->assertNull($canal->name_search_page_token);
        $this->assertNull($canal->video_check_error);
    }

    public function test_hidden_field_does_not_erase_legacy_name_search_day(): void
    {
        $canal = Canal::factory()->create(['import_day' => 3]);
        $this->actingAs($this->actor($canal, 'admin'))
            ->put('/dashboard/canals/'.$canal->id, $this->payload($canal, ['description' => 'Nový popis']))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(3, $canal->fresh()->import_day);
    }

    public function test_form_hides_schedule_without_channel_and_offers_front_list_to_superadmin(): void
    {
        $canal = Canal::factory()->create();
        $this->actingAs($this->actor($canal, 'superadmin'));
        $response = $this->get('/dashboard/canals/'.$canal->id.'/edit')->assertOk();
        $response->assertSee('name="front_listed"', false);
        $this->assertMatchesRegularExpression('/data-video-schedule\s+hidden/', $response->getContent());
        $this->assertMatchesRegularExpression('/name="import_day"\s+disabled/', $response->getContent());

        $canal->update(['youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw']);
        $response = $this->get('/dashboard/canals/'.$canal->id.'/edit')->assertOk();
        $this->assertDoesNotMatchRegularExpression('/data-video-schedule\s+hidden/', $response->getContent());
        $this->assertDoesNotMatchRegularExpression('/name="import_day"\s+disabled/', $response->getContent());
    }
}
