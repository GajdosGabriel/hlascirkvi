<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Skryje modlitby bez obsahu — text kratší ako 3 znaky („G", „ok", „🙂",
 * prázdny reťazec). V 9/2026 ich bolo 12. Nedajú sa ani upraviť, lebo text
 * musí mať aspoň 3 znaky.
 *
 * Skrývajú sa mäkkým zmazaním s pevným časom, podľa ktorého down() obnoví
 * presne tieto záznamy a nie modlitby zmazané inou cestou.
 */
return new class extends Migration
{
    private const HIDDEN_AT = '2026-09-19 15:00:00';

    public function up(): void
    {
        DB::table('prayers')
            ->whereNull('deleted_at')
            ->whereRaw("char_length(trim(coalesce(body, ''))) < 3")
            ->update(['deleted_at' => self::HIDDEN_AT]);
    }

    public function down(): void
    {
        DB::table('prayers')
            ->where('deleted_at', self::HIDDEN_AT)
            ->update(['deleted_at' => null]);
    }
};
