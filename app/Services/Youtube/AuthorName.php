<?php

namespace App\Services\Youtube;

use Illuminate\Support\Str;

/**
 * Meno autora komentára z YouTube. API namiesto zobrazovaného mena vracia
 * handle („@PatriciaButelova", obsadený handle aj s náhodnou príponou
 * „@AnnaKravcová-r3w"). Z takého handle sa dá meno odhadnúť: prípona sa
 * zahodí a slová spojené veľkým písmenom sa oddelia („Patricia Butelova").
 * Handle s číslami či inými znakmi („mirkaculagova2709") ostáva, ako je.
 */
class AuthorName
{
    public static function fromHandle(?string $handle): ?string
    {
        $name = ltrim(trim((string) $handle), '@');

        // Náhodná prípona obsadeného handle: pomlčka a 3–5 malých písmen s číslicou.
        $name = preg_replace('/-(?=[a-z0-9]{0,4}\d)[a-z0-9]{3,5}$/', '', $name);

        if (preg_match('/^\p{L}+$/u', $name) && preg_match('/\p{Ll}\p{Lu}/u', $name)) {
            $name = collect(preg_split('/(?<=\p{Ll})(?=\p{Lu})/u', $name))
                ->map(fn ($word) => Str::ucfirst($word))
                ->implode(' ');
        }

        return Str::limit($name, 100, '') ?: null;
    }
}
