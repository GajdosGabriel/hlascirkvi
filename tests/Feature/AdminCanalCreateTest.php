<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCanalCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);

        return $admin;
    }

    public function test_create_page_uses_full_form_and_list_links_to_it(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.canal.create'))
            ->assertOk()
            ->assertSee(route('admin.canal.store'), false)
            ->assertSee('Vytvoriť kanál')
            ->assertSee('name="users_submitted"', false)
            ->assertSee('name="published"', false)
            ->assertSee('name="youtube_channel"', false)
            ->assertDontSee('name="_method"', false);

        $this->get(route('admin.canal.index'))
            ->assertOk()
            ->assertSee(route('admin.canal.create'), false)
            ->assertDontSee('<new-canal', false);
    }

    public function test_store_saves_all_edit_fields_and_selected_managers(): void
    {
        $admin = $this->admin();
        $manager = User::factory()->create();
        $this->actingAs($admin)->post(route('admin.canal.store'), [
            'title' => 'Nový testovací kanál',
            'description' => '<p>Popis kanála</p>',
            'village_id' => Village::factory()->create()->id,
            'email' => 'kontakt@example.sk',
            'phone' => '+421 900 123 456',
            'street' => 'Hlavná 1',
            'url_www' => 'www.example.sk',
            'identity_mode' => 'personal',
            'denomination' => 'catholic',
            'youtube_channel' => 'UC' . str_repeat('a', 22),
            'mod_title' => 'Test',
            'post_section' => 'live',
            'import_day' => 'auto',
            'published' => '',
            'front_listed' => '1',
            'users_submitted' => '1',
            'users' => [$manager->id],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.canal.show', Canal::where('title', 'Nový testovací kanál')->firstOrFail()));

        $canal = Canal::where('title', 'Nový testovací kanál')->firstOrFail();
        $this->assertNull($canal->published);
        $this->assertNotNull($canal->front_listed_at);
        $this->assertNotNull($canal->video_check_next_at);
        $this->assertContains($canal->import_day, array_keys(Canal::IMPORT_DAYS));
        $this->assertSame('live', $canal->post_section->value);
        $this->assertSame('personal', $canal->identity_mode->value);
        $this->assertSame('catholic', $canal->denomination->value);
        $this->assertSame('https://www.example.sk', $canal->url_www);
        $this->assertSame('Test', $canal->mod_title);
        $this->assertSame('Hlavná 1', $canal->street);
        $this->assertSame('kontakt@example.sk', $canal->email);
        $this->assertSame([$manager->id], $canal->users()->pluck('users.id')->all());
    }

    public function test_invalid_data_returns_to_form_and_empty_managers_are_allowed(): void
    {
        $this->actingAs($this->admin());
        $this->from(route('admin.canal.create'))->post(route('admin.canal.store'), [
            'title' => '',
            'village_id' => '',
        ])->assertRedirect(route('admin.canal.create'))
            ->assertSessionHasErrors(['title', 'village_id']);

        $this->post(route('admin.canal.store'), [
            'title' => 'Kanál bez správcu',
            'village_id' => Village::factory()->create()->id,
            'users_submitted' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertCount(0, Canal::where('title', 'Kanál bez správcu')->firstOrFail()->users);
    }

    public function test_new_youtube_source_gets_day_without_manual_selection(): void
    {
        $this->actingAs($this->admin());
        foreach ([
            ['youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw'],
            ['youtube_playlist' => 'PL' . str_repeat('a', 32), 'import_day' => ''],
            ['youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw', 'import_day' => 0],
        ] as $index => $settings) {
            $title = 'Nový zdroj ' . $index;
            $this->post(route('admin.canal.store'), $settings + [
                'title' => $title,
                'village_id' => Village::factory()->create()->id,
            ])->assertSessionHasNoErrors();

            $canal = Canal::where('title', $title)->firstOrFail();
            $this->assertNotNull($canal->import_day);
            $this->assertSame($canal->import_day, $canal->video_check_next_at->dayOfWeek);
            $this->assertSame('16:24', $canal->video_check_next_at->format('H:i'));
            if ($index === 2) {
                $this->assertSame(0, $canal->import_day);
            }
        }
    }

    public function test_dashboard_create_also_schedules_youtube_source(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('profile.canals.store'), [
            'title' => 'YouTube z nástenky',
            'village_id' => Village::factory()->create()->id,
            'youtube_channel' => 'UCtWheHmWwuokUASxXus4NBw',
        ])->assertSessionHasNoErrors();

        $canal = Canal::where('title', 'YouTube z nástenky')->firstOrFail();
        $this->assertNotNull($canal->import_day);
        $this->assertSame($canal->import_day, $canal->video_check_next_at->dayOfWeek);
        $this->assertTrue($canal->users->contains($admin));
    }

    public function test_regular_user_cannot_create_admin_channel(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.canal.create'))->assertRedirect('/');
        $this->post(route('admin.canal.store'), [
            'title' => 'Neoprávnený kanál',
            'village_id' => Village::factory()->create()->id,
        ])->assertRedirect('/');
        $this->assertDatabaseMissing('canals', ['title' => 'Neoprávnený kanál']);
    }
}
