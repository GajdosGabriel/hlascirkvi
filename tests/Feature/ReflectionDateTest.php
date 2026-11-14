<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReflectionDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_reflection_displays_its_date_in_slovak(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-04 12:00:00'));
        DB::table('verses')->insert([
            ['id' => 276, 'slug' => 'predchadzajuce', 'title' => 'Predchádzajúce zamyslenie'],
            ['id' => 277, 'slug' => 'dnes', 'title' => 'Dnešné zamyslenie'],
            ['id' => 278, 'slug' => 'dalsie', 'title' => 'Ďalšie zamyslenie'],
        ]);

        $this->get(route('verses.index', 'dalsie'))->assertOk()
            ->assertSee('Zamyslenie na 5. októbra 2026')
            ->assertDontSee('Zamyslenie na 4. októbra 2026')
            ->assertSee('Biblický verš k zamysleniu')->assertSee('Verš starej zmluvy');
        $this->get(route('verses.index'))->assertOk()->assertSee('Zamyslenie na 4. októbra 2026');
    }

    /**
     * Zamyslení je 365. Posledný deň priestupného roka (deň 366) nemal svoj
     * riadok a /zamyslenia vracali 404.
     */
    public function test_last_day_of_leap_year_still_has_a_reflection(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2028-12-31 12:00:00'));
        DB::table('verses')->insert([
            ['id' => 364, 'slug' => 'predposledne', 'title' => 'Predposledné zamyslenie'],
            ['id' => 365, 'slug' => 'posledne', 'title' => 'Posledné zamyslenie'],
        ]);

        $this->assertSame(365, \App\Models\Verse::today()?->id);
        $this->get(route('verses.index'))->assertOk()->assertSee('Posledné zamyslenie');
    }

    public function test_day_number_uses_calendar_days_across_clock_changes(): void
    {
        $controller = app(\App\Http\Controllers\VerseController::class);
        $this->assertSame('2026-10-26', $controller->date_from_day_of_year('Y-m-d', 299, 2026));
        $this->assertSame('2024-02-29', $controller->date_from_day_of_year('Y-m-d', 60, 2024));
    }
}