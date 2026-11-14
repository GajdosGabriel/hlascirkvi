<?php

namespace Tests\Feature;

use App\Enums\AnnouncementPlacement;
use App\Enums\AnnouncementVariant;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Oznamy na webe. Strážia sa dve veci: že do ich správy nevidí nikto okrem
 * superadmina a že sa na web dostanú len tie zapnuté a práve prebiehajúce —
 * vypnutý oznam totiž ostáva v tabuľke aj s textom.
 */
class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

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
                'placement' => AnnouncementPlacement::AboveMenu->value,
                'variant'   => AnnouncementVariant::Warning->value,
                'title'     => 'Odstávka webu',
                'body'      => 'V nedeľu od 8:00 do 10:00.',
                'link_url'  => 'https://www.hlascirkvi.sk/gdpr',
                'active'    => '1',
            ])
            ->assertRedirect(route('admin.announcement.index'));

        $announcement = Announcement::sole();

        $this->assertSame('Odstávka webu', $announcement->title);
        $this->assertSame(AnnouncementPlacement::AboveMenu, $announcement->placement);
        $this->get('/gdpr')->assertOk()->assertSeeInOrder(['Odstávka webu', '<nav'], false);
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

    // ------------------------------------------------------ obrázkový baner

    public function test_superadmin_nahra_obrazkovy_baner_a_web_ho_vykresli(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superadmin())
            ->post('/admin/announcement', [
                'placement'    => AnnouncementPlacement::Top->value,
                'variant'      => AnnouncementVariant::Neutral->value,
                'title'        => 'Organizujete kresťanské podujatie?',
                'link_url'     => 'https://tixio.eu',
                'active'       => '1',
                'image'        => UploadedFile::fake()->image('baner.gif', 728, 90),
                'mobile_image' => UploadedFile::fake()->image('baner-mobil.gif', 300, 250),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.announcement.index'));

        $announcement = Announcement::sole();

        $this->assertTrue($announcement->hasImage());
        Storage::disk('public')->assertExists($announcement->image_path);
        Storage::disk('public')->assertExists($announcement->mobile_image_path);
        $this->assertSame(728, $announcement->image_width);
        $this->assertSame(90, $announcement->image_height);

        $this->get('/gdpr')
            ->assertOk()
            ->assertSee($announcement->imageUrl(), false)
            ->assertSee($announcement->mobileImageUrl(), false)
            ->assertSee(route('announcement.click', $announcement), false)
            ->assertSee('target="_blank"', false);
    }

    public function test_svg_sa_ako_baner_neulozi(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superadmin())
            ->post('/admin/announcement', [
                'placement' => AnnouncementPlacement::Top->value,
                'variant'   => AnnouncementVariant::Info->value,
                'title'     => 'Pokus',
                'image'     => UploadedFile::fake()->createWithContent('x.svg', '<svg onload="alert(1)"/>'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Announcement::count());
    }

    public function test_odstranenie_obrazka_zmaze_subor(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('a.png', 300, 250)->store(Announcement::IMAGE_DIR, 'public');
        $announcement = Announcement::factory()->create(['image_path' => $path, 'image_width' => 300, 'image_height' => 250]);

        $this->actingAs($this->superadmin())
            ->put(route('admin.announcement.update', $announcement), [
                'placement'    => $announcement->placement->value,
                'variant'      => $announcement->variant->value,
                'title'        => $announcement->title,
                'active'       => '1',
                'remove_image' => '1',
            ])
            ->assertRedirect(route('admin.announcement.index'));

        $this->assertNull($announcement->fresh()->image_path);
        Storage::disk('public')->assertMissing($path);
    }

    // ------------------------------------------------------------- kliky

    public function test_preklik_zapocita_klik_raz_za_den_a_presmeruje(): void
    {
        $announcement = Announcement::factory()->create(['link_url' => 'https://tixio.eu']);
        $headers = ['User-Agent' => 'Mozilla/5.0 Firefox/140', 'Sec-Fetch-Site' => 'same-origin'];

        $this->withHeaders($headers)->get(route('announcement.click', $announcement))
            ->assertRedirect('https://tixio.eu');
        $this->withHeaders($headers)->get(route('announcement.click', $announcement))
            ->assertRedirect('https://tixio.eu');

        $this->assertSame(1, $announcement->fresh()->clicks);
    }

    public function test_robot_a_cudzi_web_klik_nenafuknu(): void
    {
        $announcement = Announcement::factory()->create(['link_url' => 'https://tixio.eu']);

        $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])
            ->get(route('announcement.click', $announcement))
            ->assertRedirect('https://tixio.eu');
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0', 'Sec-Fetch-Site' => 'cross-site'])
            ->get(route('announcement.click', $announcement))
            ->assertRedirect('https://tixio.eu');

        $this->assertSame(0, $announcement->fresh()->clicks);
    }

    public function test_vypnuty_oznam_nepresmeruje_na_cudzi_web(): void
    {
        $announcement = Announcement::factory()->inactive()->create(['link_url' => 'https://tixio.eu']);

        $this->get(route('announcement.click', $announcement))->assertRedirect('/');
    }
}
