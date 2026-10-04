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

    public function test_day_number_uses_calendar_days_across_clock_changes(): void
    {
        $controller = app(\App\Http\Controllers\VerseController::class);
        $this->assertSame('2026-10-26', $controller->date_from_day_of_year('Y-m-d', 299, 2026));
        $this->assertSame('2024-02-29', $controller->date_from_day_of_year('Y-m-d', 60, 2024));
    }
}