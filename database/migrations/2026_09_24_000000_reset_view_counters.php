<?php

use App\Services\Dashboard\AdminDashboardStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Počítadlo zobrazení začína od nuly.
 *
 * `posts.count_view` nieslo ~143 mil. zobrazení, ktoré nezodpovedali
 * skutočnosti: pôvodný Counter zvyšoval číslo pri každom načítaní stránky
 * a v 11. – 19. 9. 2026 k tomu crawler s rotujúcimi IP a user-agentmi pridal
 * ~3 mil. „návštevníkov". Odvtedy sa zobrazenie zapisuje až cez beacon
 * z prehliadača (PostController::view), takže nové čísla sú s pôvodnými
 * neporovnateľné — preto sa staré zahadzujú celé, nie odhadom.
 *
 * Nevratné — down() nič neobnovuje.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Query builder, nie model — nesmie hýbať updated_at ani spúšťať
        // observery.
        DB::table('posts')->where('count_view', '!=', 0)->update(['count_view' => 0]);

        DB::table('views')->truncate();

        // Admin prehľad drží súčet zobrazení v cache, ktorú ohrieva cron.
        Cache::forget(AdminDashboardStats::CACHE_KEY);
    }

    public function down(): void
    {
        //
    }
};
