<?php

/*
 * Buffer je fronta naimportovaných príspevkov, ktoré ešte nie sú v prednom
 * zozname. Publisher ich z nej vypúšťa po jednom počas celého dňa tak, aby to
 * pôsobilo ako ručné pridávanie — nie ako dávka v jednom okamihu.
 */
return [
    /*
     * Poistka: pri `true` sa import (App\Services\VideoUpload) vráti
     * k pôvodnému správaniu a videá kanálov zo zoznamu „default" pustí do
     * zoznamu rovno pri sťahovaní — teda celú dennú dávku naraz. Buffer
     * potom dostane len kanály mimo zoznamu.
     */
    'publish_on_import' => env('BUFFER_PUBLISH_ON_IMPORT', false),

    /*
     * Koľko príspevkov denne. Kvóta sa skladá z dvoch častí:
     *
     *  1. denný prítok — priemer za posledných `inflow_days` dní, aby front
     *     nerástol donekonečna (import prináša okolo 13 videí denne),
     *  2. rozpúšťanie starého frontu — čo leží vo fronte dlhšie ako
     *     `keep_ahead_days` prítoku, ide von tempom „za `drain_days` dní".
     *
     * Výsledok sa oreže do rozsahu min..max a nikdy nepresiahne to, čo vo
     * fronte reálne je.
     */
    'daily' => [
        'min' => 3,
        'max' => 18,
        'inflow_days' => 7,
        'keep_ahead_days' => 2,
        'drain_days' => 30,
    ],

    /*
     * Najviac toľkoto príspevkov z jedného kanála za deň. Strop musí uniesť
     * aj najsilnejší kanál (Slovenský dohovor za rodinu vozí okolo piatich
     * videí denne), inak by mu front rástol donekonečna. Ak čaká jediný
     * kanál, strop sa neuplatní — nie je čo striedať.
     */
    'max_per_organization' => 6,

    /*
     * Čo je vo fronte dlhšie ako toľkoto dní, berie publisher ako archív.
     * Archív ide von vlastnými slotmi (toľkými, koľko je v dennej kvóte nad
     * rámec prítoku), aby čerstvé videá nečakali za dvomi stovkami starých.
     */
    'archive_after_days' => 30,

    /*
     * Minimálny odstup medzi dvoma zverejneniami. Platí aj pri dobiehaní, keď
     * scheduler chvíľu nebežal — zmeškané sloty sa rozpustia postupne namiesto
     * toho, aby vyšli naraz.
     */
    'min_gap_minutes' => 25,

    /*
     * Časové okná dňa, v ktorých sa zverejňuje, s váhou (o koľko častejšie sa
     * okno vyberá). Konkrétny čas v rámci okna je náhodný, takže príspevky
     * nechodia na okrúhlu hodinu — ale pre daný deň vždy rovnaký, aby si plán
     * nemusel nikam ukladať.
     */
    'windows' => [
        ['from' => '07:05', 'to' => '09:10', 'weight' => 2], // ráno
        ['from' => '09:40', 'to' => '11:35', 'weight' => 2], // dopoludnia
        ['from' => '12:05', 'to' => '13:45', 'weight' => 3], // obed
        ['from' => '14:10', 'to' => '16:15', 'weight' => 2], // poobede
        ['from' => '16:50', 'to' => '19:05', 'weight' => 3], // podvečer
        ['from' => '19:30', 'to' => '21:10', 'weight' => 2], // večer
    ],

    /*
     * Víkend má iný rytmus: ráno sa nikam neponáhľa a v nedeľu dopoludnia
     * (bohoslužby) sa nezverejňuje vôbec — ťažisko je poobede a večer.
     */
    'windows_weekend' => [
        ['from' => '08:30', 'to' => '10:30', 'weight' => 1],
        ['from' => '13:00', 'to' => '15:30', 'weight' => 3],
        ['from' => '16:00', 'to' => '18:30', 'weight' => 3],
        ['from' => '19:00', 'to' => '21:00', 'weight' => 2],
    ],

    'windows_sunday' => [
        ['from' => '12:30', 'to' => '14:40', 'weight' => 2],
        ['from' => '15:10', 'to' => '17:30', 'weight' => 3],
        ['from' => '18:00', 'to' => '20:45', 'weight' => 3],
    ],
];
