<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Oslovenie v e-mailoch („Dobrý deň, …,") sa bralo z first_names.vocative, kde
 * sú české tvary — „Mário", „Evo", „Petře". Slovenčina oslovuje nominatívom,
 * takže UserObserver už ukladá meno tak, ako si ho užívateľ napísal; tu sa
 * rovnako prepíšu skôr uložené riadky.
 *
 * Postup je ten istý ako v observeri: ak je medzi krstnými menami first_name,
 * oslovením je ono, inak last_name (prehodené meno a priezvisko). Kde nesedí
 * ani jedno — meno medzičasom nahradené maskovaným tvarom „A•••j", ktorému
 * ostalo staré „Andreji" — oslovenie sa vyprázdni a e-mail začne neutrálnym
 * „Dobrý deň,". down() nie je: český tvar nemá zmysel vracať.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->select(['id', 'first_name', 'last_name', 'vocative'])
            ->whereNotNull('vocative')
            ->orderBy('id')
            ->chunkById(500, function ($users) {
                foreach ($users as $user) {
                    $name = collect([$user->first_name, $user->last_name])
                        ->map(fn ($value) => trim((string) $value))
                        ->first(fn ($value) => $value !== '' && DB::table('first_names')->where('name', $value)->exists());

                    if ($name !== $user->vocative) {
                        DB::table('users')->where('id', $user->id)->update(['vocative' => $name]);
                    }
                }
            });
    }

    public function down(): void
    {
        //
    }
};
