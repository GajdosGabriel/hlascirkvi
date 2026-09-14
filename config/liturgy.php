<?php

/*
 * Liturgické čítania na každý deň — modul v bočnom paneli a stránka
 * /citania. Kostru kalendára (obdobie, týždeň, cyklus A/B/C a I/II) počíta
 * App\Services\Liturgy\LiturgicalCalendar, konkrétne perikopy a sviatky
 * svätých sa sťahujú z liturgického kalendára KBS do `liturgical_days`.
 */
return [
    'source_url' => env('LITURGY_SOURCE_URL', 'https://lc.kbs.sk/'),

    /*
     * Plné znenie čítaní (preklad KBS) — rozbalí sa po kliknutí na citáciu
     * v module a je vypísané na /citania. Vypnutím ostanú len citácie,
     * nadpisy a odkaz na KBS. Po zapnutí doplní texty k starším dňom
     * príkaz liturgia:stiahnut --znova.
     */
    'full_texts' => (bool) env('LITURGY_FULL_TEXTS', true),

    /* Koľko dní dopredu drží príkaz liturgia:stiahnut v databáze. */
    'days_ahead' => 45,

    /* Deň stiahnutý pred viac než toľkými dňami sa stiahne znova — KBS občas opraví preklep. */
    'refresh_after_days' => 14,

    /* Prestávka medzi požiadavkami na KBS, nech ich server nezahlcujeme. */
    'pause_ms' => 1000,

    'timeout' => 10,

    /*
     * Keď deň v databáze chýba, stránka si ho skúsi stiahnuť sama — ale len
     * s krátkym časovým limitom, aby výpadok KBS nespomalil úvodnú stránku.
     * Neúspech sa pamätá `retry_minutes` minút.
     */
    'lazy_fetch' => (bool) env('LITURGY_LAZY_FETCH', true),
    'lazy_timeout' => 4,
    'retry_minutes' => 10,

    /*
     * „Homílie z archívu": videá zverejnené v deň, keď sa naposledy čítalo to
     * isté nedeľné evanjelium (cyklus sa opakuje po 3 rokoch), ± `homily_window_days`.
     * Berú sa nedeľné prenosy a príspevky, ktorých názov obsahuje niektoré
     * z kľúčových slov (LIKE, databáza ignoruje diakritiku aj veľkosť písmen).
     */
    'homily_years_back' => [3, 6, 9],
    'homily_window_days' => 2,
    'homily_limit' => 3,
    'homily_keywords' => ['homíli', 'kázeň', 'kázne', 'nedeľ', 'evanjel', 'omša', 'omše'],
];
