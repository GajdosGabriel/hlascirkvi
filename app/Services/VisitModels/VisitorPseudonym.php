<?php

namespace App\Services\VisitModels;

use Illuminate\Http\Request;

/**
 * Pseudonym anonymného návštevníka — sha256 z IP, user-agenta, aplikačného
 * kľúča a dnešného dátumu.
 *
 * IP sa nikam neukladá, cookie sa nenastavuje, a keďže je v hashi dátum,
 * pseudonym sa každý deň mení. Z tabuľky `views` sa teda nedá poskladať, čo
 * konkrétny človek čítal naprieč dňami.
 *
 * Poradie polí v hashi nemeňte — v deň nasadenia by sa každému návštevníkovi
 * zmenil pseudonym a počítadlo by ten deň rátalo dvakrát.
 */
final class VisitorPseudonym
{
    public static function forRequest(Request $request): string
    {
        return hash('sha256', implode('|', [
            (string) $request->ip(),
            (string) $request->userAgent(),
            (string) config('app.key'),
            now()->toDateString(),
        ]));
    }
}
