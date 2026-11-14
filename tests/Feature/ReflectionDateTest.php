<?php

namespace Tests\Feature;

use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReflectionDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_reflection_displays_its_date_in_slovak(): void
    {
        $this->travelTo(Carbon::parse('2026-10-04 12:00:00'));
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
     * 31. december má aj v priestupnom roku zamyslenie č. 365 (predtým deň
     * 366 nemal riadok a /zamyslenia vracali 404).
     */
    public function test_last_day_of_leap_year_still_has_a_reflection(): void
    {
        $this->travelTo(Carbon::parse('2028-12-31 12:00:00'));
        DB::table('verses')->insert([
            ['id' => 364, 'slug' => 'predposledne', 'title' => 'Predposledné zamyslenie'],
            ['id' => 365, 'slug' => 'posledne', 'title' => 'Posledné zamyslenie'],
        ]);

        $this->assertSame(365, Verse::today()?->id);
        $this->get(route('verses.index'))->assertOk()
            ->assertSee('Posledné zamyslenie')->assertSee('Zamyslenie na 31. decembra 2028');
    }

    /**
     * Zamyslenie patrí k mesiacu a dňu, nie k poradiu dňa v roku: 1. marec
     * má v priestupnom aj nepriestupnom roku to isté, 29. február zopakuje 28.
     */
    public function test_reflection_is_bound_to_month_and_day_in_leap_year(): void
    {
        DB::table('verses')->insert([
            ['id' => 59, 'slug' => 'februar-28', 'title' => 'Zamyslenie 28. februára'],
            ['id' => 60, 'slug' => 'marec-1', 'title' => 'Zamyslenie 1. marca'],
            ['id' => 61, 'slug' => 'marec-2', 'title' => 'Zamyslenie 2. marca'],
        ]);

        $this->assertSame(60, Verse::forDate(Carbon::parse('2027-03-01'))->id);
        $this->assertSame(60, Verse::forDate(Carbon::parse('2028-03-01'))->id);
        $this->assertSame(59, Verse::forDate(Carbon::parse('2028-02-28'))->id);
        $this->assertSame(59, Verse::forDate(Carbon::parse('2028-02-29'))->id);

        $this->travelTo(Carbon::parse('2028-02-29 12:00:00'));
        $this->get(route('verses.index'))->assertOk()
            ->assertSee('Zamyslenie 28. februára')->assertSee('Zamyslenie na 29. februára 2028');

        $this->travelTo(Carbon::parse('2028-03-01 12:00:00'));
        $this->get(route('verses.index'))->assertOk()->assertSee('Zamyslenie 1. marca');
        $this->get(route('verses.index', 'marec-1'))->assertOk()
            ->assertSee('Zamyslenie na 1. marca 2028')
            ->assertSee('zamyslenia/februar-28')->assertSee('zamyslenia/marec-2');
    }

    public function test_reflection_number_maps_to_calendar_date(): void
    {
        $this->assertSame('2026-10-26', Verse::dateFor(299, 2026)->format('Y-m-d'));
        $this->assertSame('2024-03-01', Verse::dateFor(60, 2024)->format('Y-m-d'));
        $this->assertSame('2024-02-28', Verse::dateFor(59, 2024)->format('Y-m-d'));
        $this->assertSame('2024-12-31', Verse::dateFor(365, 2024)->format('Y-m-d'));
    }
}
