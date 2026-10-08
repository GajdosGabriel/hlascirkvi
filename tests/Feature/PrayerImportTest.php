<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Import modlitieb zo zdrojov (App\Services\Extractor).
 *
 * Vkladá cez DB::table, teda mimo Prayer::booted(). Po pridaní stĺpca
 * `published` ostávali importované modlitby dva týždne skryté.
 */
class PrayerImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_imported_prayers_are_published(): void
    {
        Http::fake(['www.zzm.sk/*' => Http::response(
            '<html><body><div class="gb-author-name">Mária</div>'
            . '<div class="gb-entry-content">Prosím o modlitbu za uzdravenie mamy.</div></body></html>'
        )]);

        $this->artisan('prayer:zdruzenieMedaily')->assertSuccessful();

        $prayer = DB::table('prayers')->first();
        $this->assertNotNull($prayer);
        $this->assertSame($prayer->created_at, $prayer->published);

        $this->getJson('/api/prayers')->assertOk()->assertJsonCount(1, 'data');
    }
}
