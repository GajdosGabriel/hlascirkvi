<?php

/*
 * Predný zoznam kanálov — karta „Kresťanské osobnosti" v bočnom paneli
 * úvodnej stránky. Zaradenie a poradie drží samotný kanál
 * (`organizations.front_listed_at`, `front_position`), spravuje sa
 * v /admin/front-list.
 */
return [
    /*
     * Koľko kanálov karta v bočnom paneli ukáže. Zvyšok je za odkazom na
     * celý zoznam — bez stropu karta rástla s každým pridaným kanálom
     * (v čase prepisu ich bolo 37) a bočný panel bol dvakrát dlhší ako
     * mriežka príspevkov vedľa neho.
     */
    'card_limit' => 12,

    /*
     * Kanál, z ktorého nič nevyšlo dlhšie ako toľkoto mesiacov, označí admin
     * obrazovka ako spiaci. Na titulke viseli roky kanály, ktoré naposledy
     * niečo vydali v roku 2017.
     */
    'stale_after_months' => 12,

    /*
     * Ako dlho sa zoznam drží v cache. Mení sa ručne, párkrát do roka —
     * ale počty príspevkov pri ňom rastú každý deň, preto nie navždy.
     * Po zásahu v admine sa cache zahodí sama (App\Services\FrontList).
     */
    'cache_minutes' => 60,
];
