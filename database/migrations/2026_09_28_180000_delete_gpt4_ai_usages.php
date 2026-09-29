<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Modely gpt-4* sa už nepoužívajú — z evidencie volaní sa zahadzujú
 * (68 volaní obohacovania kanálov cez gpt-4.1-mini-2025-04-14).
 *
 * Nevratné — down() nič neobnovuje.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_usages')->where('model', 'like', 'gpt-4%')->delete();
    }

    public function down(): void
    {
        //
    }
};
