<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $stamp = now();
            foreach ([
                ['posts', 'title', 'TEST XSS Claude', '2026-10-01 22:05:33'],
                ['posts', 'title', 'TEST článok s obrázkom Claude', '2026-10-01 22:05:34'],
                ['prayers', 'body', 'TEST úmysel (Claude) - možno zmazať', '2026-10-01 21:37:50'],
                ['comments', 'body', 'TEST komentár (Claude) - UPRAVENÉ, možno zmazať', '2026-10-01 21:27:23'],
                ['comments', 'body', 'TEST odpoveď 1 (Claude) - možno zmazať', '2026-10-01 21:29:06'],
                ['comments', 'body', '@Gabriel Gajdoš TEST odpoveď 2 na odpoveď (Claude) - možno zmazať', '2026-10-01 21:29:47'],
            ] as [$table, $field, $text, $createdAt]) {
                // Presný obsah a čas; všeobecné slovo „test“ nestačí.
                DB::table($table)->where($field, $text)->where('created_at', $createdAt)
                    ->whereNull('deleted_at')->update(['deleted_at' => $stamp, 'updated_at' => $stamp]);
            }
        });
    }

    public function down(): void
    {
        // Obsah zostáva v databáze a možno ho jednotlivo obnoviť.
        // Rollback kódu nemá znovu zverejniť bezpečnostné testovacie články.
    }
};
