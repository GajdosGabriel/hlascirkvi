<?php

/*
 * Predný zoznam kanálov — karty „Kresťanské osobnosti" a „Cirkvi
 * a spoločenstvá" v bočnom paneli. Kto v zozname je a akého je typu, drží
 * samotný kanál (`organizations.front_listed_at`, `kind`), spravuje sa
 * v /admin/front-list. Poradie na karte je automatické.
 */
return [
    /*
     * Koľko kanálov jedna karta ukáže. Zvyšok je za odkazom na celý zoznam.
     */
    'card_limit' => 8,

    /*
     * Z miest na karte toľkoto patrí kanálom, ktoré práve niečo vydali, no
     * v rebríčku záujmu sa navrch nedostali. Ktoré to budú, sa strieda každý
     * deň — inak by karta ukazovala stále tých istých najväčších.
     */
    'discovery_slots' => 2,

    /* Za „práve vydal" sa počíta príspevok zverejnený za toľkoto dní. */
    'discovery_days' => 14,

    /*
     * Rebríček záujmu: zhliadnutia príspevkov kanála a noví sledovatelia za
     * posledných `window_days` dní. Každý deň staroby polovicu váhy stratí
     * po `half_life_days` dňoch — zhliadnutie spred týždňa má polovičnú váhu,
     * spred dvoch týždňov štvrtinovú. Kto prestane zaujímať, z vrchu zíde sám.
     *
     * Okno musí byť kratšie ako to, čo z tabuľky `views` ponecháva
     * app:views-prune (90 dní).
     */
    'window_days'    => 28,
    'half_life_days' => 7,

    /*
     * Jeden nový sledovateľ kanála váži ako toľkoto zhliadnutí. Sledovať
     * kanál je silnejší prejav záujmu ako otvoriť jedno video.
     */
    'follow_weight' => 10,

    /*
     * Kanál, z ktorého nič nevyšlo dlhšie ako toľkoto mesiacov, označí admin
     * obrazovka ako spiaci.
     */
    'stale_after_months' => 12,

    /*
     * Ako dlho sa zoznam aj s rebríčkom drží v cache. Po zásahu v admine sa
     * cache zahodí sama (App\Services\FrontList).
     */
    'cache_minutes' => 60,
];
