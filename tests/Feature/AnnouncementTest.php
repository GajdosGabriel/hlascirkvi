<?php

namespace Tests\Feature;

use App\Enums\AnnouncementPlacement;
use App\Enums\AnnouncementVariant;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Oznamy na webe. Strážia sa dve veci: že do ich správy nevidí nikto okrem
 * superadmina a že sa na web dostanú len tie zapnuté a práve prebiehajúce —
 * vypnutý oznam totiž ostáva v tabuľke aj s textom.
 */
class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bez rolí sa nedá založiť ani jeden užívateľ (UserObserver::created).
        $this->seed(RolesSeeder::class);
    }

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user->fresh();
    }

    // ------------------------------------------------------------- prístup

    public function test_host_sa_k_sprave_oznamov_nedostane(): void
    {
        $this->get('/admin/announcement')->assertRedirect(route('login'));
    }

    public function test_bezny_uzivatel_sa_k_sprave_oznamov_nedostane(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/announcement')
            ->assertRedirect('/');
    }

    public function test_superadmin_vidi_vypis_oznamov(): void
    {
        $announcement = Announcement::factory()->create(['title' => 'Prenos polnočnej omše']);

        $this->actingAs($this->superadmin())
            ->get('/admin/announcement')
            ->assertOk()
            ->assertSee($announcement->title);
    }

    public function test_superadmin_otvori_oba_formulare(): void
    {
        $announcement = Announcement::factory()->create();
        $superadmin = $this->superadmin();

        $this->actingAs($superadmin)->get('/admin/announcement/create')->assertOk();

        $this->actingAs($superadmin)
            ->get(route('admin.announcement.edit', $announcement))
            ->assertOk()
            ->assertSee($announcement->title);
    }

    // ------------------------------------------------------------- zápis

    public function test_superadmin_zalozi_oznam(): void
    {
        $this->actingAs($this->superadmin())
            ->post('/admin/announcement', [
                'placement' => AnnouncementPlacement::Home->value,
                'variant'   => AnnouncementVariant::Warning->value,
                'title'     => 'Odstávka webu',
                'body'      => 'V nedeľu od 8:00 do 10:00.',
                'link_url'  => 'https://www.hlascirkvi.sk/gdpr',
                'active'    => '1',
            ])
            ->assertRedirect(route('admin.announcement.index'));

        $announcement = Announcement::sole();

        $this->assertSame('Odstávka webu', $announcement->title);
        $this->assertSame(AnnouncementPlacement::Home, $announcement->placement);
        $this->assertTrue($announcement->active);
        // Nezaškrtnuté políčko formulár posiela ako "0", nie ako chýbajúcu hodnotu.
        $this->assertFalse($announcement->dismissible);
    }

    public function test_odkaz_mimo_http_sa_neulozi(): void
    {
        $this->actingAs($this->superadmin())
            ->post('/admin/announcement', [
                'placement' => AnnouncementPlacement::Top->value,
                'variant'   => AnnouncementVariant::Info->value,
                'title'     => 'Pokus',
                'link_url'  => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('link_url');

        $this->assertSame(0, Announcement::count());
    }

    public function test_prepinac_zapina_a_vypina_oznam(): void
    {
        $announcement = Announcement::factory()->create();

        $this->actingAs($this->superadmin())
            ->put(route('admin.announcement.toggle', $announcement))
            ->assertRedirect();

        $this->assertFalse($announcement->fresh()->active);
    }

    // ------------------------------------------------------- výpis na webe

    public function test_na_web_idu_len_zapnute_a_prebiehajuce_oznamy(): void
    {
        $visible = Announcement::factory()->create(['title' => 'Beží']);
        Announcement::factory()->inactive()->create(['title' => 'Vypnutý']);
        Announcement::factory()->expired()->create(['title' => 'Po termíne']);
        Announcement::factory()->scheduled()->create(['title' => 'Ešte nezačal']);

        $titles = Announcement::visibleFor(AnnouncementPlacement::Top)->pluck('title');

        $this->assertEquals(['Beží'], $titles->all());
        $this->assertTrue($visible->isRunning());
    }

    public function test_oznamy_sa_nemiesaju_medzi_miestami(): void
    {
        Announcement::factory()->create(['placement' => AnnouncementPlacement::Top]);
        Announcement::factory()->create(['placement' => AnnouncementPlacement::Sidebar]);

        $this->assertCount(1, Announcement::visibleFor(AnnouncementPlacement::Top));
        $this->assertCount(1, Announcement::visibleFor('sidebar'));
        $this->assertCount(0, Announcement::visibleFor('nezmysel'));
    }

    public function test_oznamy_sa_radia_podla_poradia(): void
    {
        Announcement::factory()->create(['title' => 'Druhý', 'sort_order' => 10]);
        Announcement::factory()->create(['title' => 'Prvý', 'sort_order' => 1]);

        $this->assertEquals(
            ['Prvý', 'Druhý'],
            Announcement::visibleFor(AnnouncementPlacement::Top)->pluck('title')->all()
        );
    }
}
