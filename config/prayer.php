<?php

/*
|--------------------------------------------------------------------------
| Zdroje modlitbových úmyslov
|--------------------------------------------------------------------------
|
| Organizácie, ktorých stránky sa pravidelne čítajú (App\Services\Extractor).
| Plánovač (App\Console\Kernel) prejde tento zoznam; nový zdroj je teda
| záznam tu, nie ďalší riadok v Kernel.php.
|
|   command  Artisan príkaz zdroja
|   enabled  false = zdroj sa neplánuje
|   minute   minúta v hodine, kedy príkaz beží
*/

return [
    'sources' => [
        'zdruzenie-medaily' => [
            'command' => 'prayer:zdruzenieMedaily',
            'enabled' => true,
            'minute' => 0,
        ],
        'sluzobnice-ducha-svateho' => [
            'command' => 'prayer:sluzobniceDuchaSvateho',
            'enabled' => true,
            'minute' => 0,
        ],
        // Vypnuté, lebo sa opakuje.
        'moja-komunita' => [
            'command' => 'prayer:mojaKomunita',
            'enabled' => false,
            'minute' => 45,
        ],
    ],
];
