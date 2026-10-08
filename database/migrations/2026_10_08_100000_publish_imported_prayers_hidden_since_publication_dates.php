<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Zverejní modlitby z importu (App\Services\Extractor), ktoré od 24. 9. 2026
 * ostali skryté: migrácia add_publication_dates_to_comments_and_prayers pridala
 * stĺpec `published`, no Extractors::createPrayer ho pri vkladaní nevypĺňal.
 *
 * Import nevypĺňa ani `updated_at`, podľa toho sa jeho záznamy rozoznajú od
 * modlitieb, ktoré skryl superadmin ručne (úprava cez Eloquent ho nastaví).
 *
 * down() nič nevracia — skrytie bola chyba, nie stav, ku ktorému sa treba vrátiť.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('prayers')
            ->whereNull('published')
            ->whereNull('updated_at')
            ->whereNull('deleted_at')
            ->whereIn('canal_id', [649, 650])
            ->update(['published' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        //
    }
};
