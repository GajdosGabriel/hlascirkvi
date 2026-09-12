<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Updater;
use App\Repositories\Eloquent\EloquentCanalRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Denné importy z YouTube sa delili o tie isté kanály: `UserSearchByName`
 * hľadal fulltextom podľa názvu organizácie aj vtedy, keď mala vyplnený
 * `youtube_channel`, ktorý o pár hodín neskôr spracoval
 * `UserSearchByChannelAndPlaylist`. Okrem dvojitého importu a sto jednotiek
 * kvóty navyše nie je hľadanie podľa mena obmedzené na kanál, takže vedelo
 * priradiť cudzie video, ktoré len obsahovalo názov organizácie.
 *
 * Testy strážia, že sa obe množiny neprekrývajú a že ani jedna organizácia
 * nevypadne z oboch.
 */
class VideoSearchByNameScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Repozitár sa pýta na dnešný deň v týždni.
        Carbon::setTestNow(Carbon::parse('2026-09-12 06:55:00')); // sobota
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function canalForSaturday(array $attributes): Canal
    {
        $updater = Updater::firstOrCreate(['slug' => 'sobota'], ['title' => 'Sobota', 'type' => 'post']);

        $canal = Canal::factory()->create($attributes);
        $canal->updaters()->attach($updater);

        return $canal;
    }

    public function test_hladanie_podla_mena_preskoci_organizacie_s_kanalom_alebo_playlistom(): void
    {
        $bezVsetkeho = $this->canalForSaturday(['youtube_channel' => null, 'youtube_playlist' => null]);
        $prazdneRetazce = $this->canalForSaturday(['youtube_channel' => '', 'youtube_playlist' => '']);
        $sKanalom = $this->canalForSaturday(['youtube_channel' => 'UCznO9E4iMXuDyTbJr5e26tg']);
        $sPlaylistom = $this->canalForSaturday(['youtube_playlist' => 'PLFgquLnL59alCl_2TQvOiD5Vgm1hCaGSI']);

        $ids = (new EloquentCanalRepository())->getUsersByDayOfWeek()->pluck('id');

        $this->assertContains($bezVsetkeho->id, $ids);
        $this->assertContains($prazdneRetazce->id, $ids);
        $this->assertNotContains($sKanalom->id, $ids);
        $this->assertNotContains($sPlaylistom->id, $ids);
    }

    public function test_obe_mnoziny_su_navzajom_doplnkom(): void
    {
        $this->canalForSaturday(['youtube_channel' => null, 'youtube_playlist' => null]);
        $this->canalForSaturday(['youtube_channel' => 'UCznO9E4iMXuDyTbJr5e26tg']);
        $this->canalForSaturday(['youtube_playlist' => 'PLFgquLnL59alCl_2TQvOiD5Vgm1hCaGSI']);

        $repository = new EloquentCanalRepository();
        $podlaMena = $repository->getUsersByDayOfWeek()->pluck('id');
        $podlaKanala = $repository->getYoutubeVideos()->pluck('id');

        $this->assertEmpty($podlaMena->intersect($podlaKanala), 'Organizácia sa importuje dvomi behmi naraz.');

        // Sobotňajšie organizácie musia byť pokryté práve jedným z behov.
        $sobotne = Canal::whereHas('updaters', fn ($query) => $query->whereSlug('sobota'))->pluck('id');
        $this->assertEmpty($sobotne->diff($podlaMena->merge($podlaKanala)), 'Organizácia vypadla z oboch behov.');
    }
}
