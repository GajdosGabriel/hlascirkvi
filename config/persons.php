<?php

/*
 * Rozpoznávanie osôb vo videách (App\Services\Persons\PersonDetector).
 */
return [

    // Video z osobného kanála bez iného mena dostane osobu kanála. Tieto
    // osobné kanály však mixujú aj cudzie videá (fanúšikovské kanály,
    // politické správy, nesúvisiaci obsah), preto sa na nich to pravidlo
    // nepoužije. Kľúčom je slug kanála.
    'canal_default_exclude' => [
        'kuffa-marian',
        'kodet-vojtech',
        'adam-emil',
    ],

    // Priezviská, ktoré sú zároveň bežným slovom, menom národa či mesta.
    // Samé (bez krstného mena) sa v názve videa za meno nepovažujú.
    'ambiguous_surnames' => [
        'slovak', 'moudry', 'kral', 'pastor', 'biskup', 'martin', 'petro', 'vasil',
        'svoboda', 'novak', 'maly', 'velky', 'cerny', 'biely', 'sokol', 'kostka',
        'king', 'jezisova', 'kniha', 'show', 'pastorka', 'koren', 'pastory', 'sarissky',
    ],

    // Koľko videí v kanáli musí osoba mať, aby „pôsobila" v ňom
    // (canal_person so zdrojom derived).
    'canal_min_posts' => 5,

    // Koľko nespracovaných príspevkov prejde jeden beh persons:detect.
    'batch' => 200,
];
