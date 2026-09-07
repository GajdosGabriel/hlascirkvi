<?php

/*
|--------------------------------------------------------------------------
| Event portál (event.hlascirkvi.sk)
|--------------------------------------------------------------------------
|
| Podujatia sa už nedržia v lokálnej databáze — verejný výpis aj detail
| na /akcie sa ťahajú cez REST API z portálu Event. Odpovede sa cachujú,
| aby každé zobrazenie stránky nešlo po sieti.
|
*/

return [

    // Základ verejného API. Bez lomítka na konci.
    'url' => rtrim(env('EVENT_PORTAL_URL', 'https://event.hlascirkvi.sk'), '/'),

    // Koľko sekúnd čakáme na odpoveď. Radšej krátko: keď portál mlčí,
    // ukážeme poslednú známu odpoveď (viď `stale_ttl`) než aby sa stránka
    // sekla na pol minúty.
    'timeout' => (int) env('EVENT_PORTAL_TIMEOUT', 8),

    // Bežná životnosť odpovede v cache.
    'ttl' => [
        'index' => (int) env('EVENT_PORTAL_TTL_INDEX', 600),      // 10 min
        'show' => (int) env('EVENT_PORTAL_TTL_SHOW', 1800),       // 30 min
        'taxonomy' => (int) env('EVENT_PORTAL_TTL_TAXONOMY', 3600), // 1 h — štítky a obce
    ],

    // Záložná kópia poslednej úspešnej odpovede. Použije sa len vtedy, keď
    // API nedostupné alebo vráti chybu — návštevník tak vidí trochu staršie
    // podujatia namiesto prázdnej stránky.
    'stale_ttl' => (int) env('EVENT_PORTAL_STALE_TTL', 604800), // 7 dní

    // Koľko podujatí na stranu vo verejnom výpise.
    'per_page' => (int) env('EVENT_PORTAL_PER_PAGE', 20),

];
