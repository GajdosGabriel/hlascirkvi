<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Skryje komentáre s priamymi vulgarizmami alebo osobnými urážkami.
 *
 * Záznamy sa nemažú natrvalo. Pevný čas umožňuje pri rollbacku obnoviť
 * výhradne komentáre upravené touto migráciou.
 */
return new class extends Migration
{
    private const HIDDEN_AT = '2026-09-25 03:00:00';

    /** @var string */
    private const ABUSIVE_TERMS = 'debil|idiot|kokot|pič|jebnut|kurv|darmožr|vysral|sere|srať|srat|blbec|blbeček|tupec|hovedo|imbecil|kretén|pablb|zmetek|zmrd|dumbfuck|fuck off|fuck you|little shit|stank breath|retarded|chrapoun|vychcánek|somar|sprosté piče|sproste piče';

    public function up(): void
    {
        DB::table('comments')
            ->whereNull('deleted_at')
            ->whereRaw('lower(body) regexp ?', [self::ABUSIVE_TERMS])
            ->update(['deleted_at' => self::HIDDEN_AT]);
    }

    public function down(): void
    {
        DB::table('comments')
            ->where('deleted_at', self::HIDDEN_AT)
            ->update(['deleted_at' => null]);
    }
};
