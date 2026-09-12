<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Náhrada za „Som človek 7 plus 3 =".
 *
 * Počítanie bolo prekážkou pre návštevníka, nie pre automat — jeden natvrdo
 * daný výsledok sa do skriptu dopĺňa raz a navždy. (V registrácii sa navyše
 * porovnával s reťazcom "10sdfasdfasdf", takže formulár neprešiel ani človeku.)
 *
 * Namiesto toho dve kontroly, ktoré návštevník nikdy neuvidí:
 *
 *  - pasca: pole odsunuté mimo plochy stránky. Človek doň nemá ako písať,
 *    automat prechádzajúci všetky <input> ho vyplní.
 *  - pečiatka času: formulár nesie zašifrovaný okamih svojho vykreslenia.
 *    Odoslanie do pár sekúnd nie je ľudské tempo; pečiatka staršia než pár
 *    hodín znamená dávno otvorenú (alebo do skriptu vytiahnutú) stránku.
 *
 * Pečiatku šifruje APP_KEY, takže sa nedá vyrobiť zvonku, a platí len po
 * obmedzený čas. Na konkrétne spojenie ju viazať netreba — to už robí CSRF
 * token, ktorý je v tom istom formulári.
 */
final class HumanCheck
{
    /** Meno poľa-pasce. Musí prísť prázdne. */
    public const TRAP = 'website';

    /** Meno poľa s pečiatkou vykreslenia. */
    public const STAMP = 'form_ts';

    /** Rýchlejšie vypĺňanie než toto už nie je ručné. */
    private const MIN_SECONDS = 3;

    /** Po tomto čase pokladáme otvorený formulár za opustený. */
    private const MAX_SECONDS = 7200;

    public static function stamp(): string
    {
        return Crypt::encryptString((string) time());
    }

    /**
     * Vráti hlášku o tom, čo na odoslaných dátach nesedí, alebo null.
     *
     * @param  array<string, mixed>  $data
     */
    public static function problem(array $data): ?string
    {
        if (filled($data[self::TRAP] ?? null)) {
            return 'Formulár vyzerá ako odoslaný automatom.';
        }

        try {
            $issuedAt = (int) Crypt::decryptString((string) ($data[self::STAMP] ?? ''));
        } catch (DecryptException) {
            return 'Platnosť formulára vypršala, obnovte stránku a skúste to znova.';
        }

        $age = time() - $issuedAt;

        if ($age < self::MIN_SECONDS) {
            return 'Formulár bol odoslaný priskoro. Skúste to o chvíľu znova.';
        }

        if ($age > self::MAX_SECONDS) {
            return 'Formulár bol otvorený príliš dlho, obnovte stránku a skúste to znova.';
        }

        return null;
    }
}
