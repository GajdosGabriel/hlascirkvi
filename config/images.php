<?php

return [

    /*
     * Disk, na ktorý sa ukladajú varianty obrázkov. Držíme sa „public",
     * pretože naň ukazuje aj symlink v public/storage.
     */
    'disk' => env('IMAGES_DISK', 'public'),

    /*
     * Kvalita zápisu. JPEG je zámerne nižší ako Interventionom predvolených
     * 75 -> 82 kvôli fotkám s plochými plochami (titulky vo videách), WebP
     * pri 80 vyjde menší než JPEG pri 82 pri rovnakom dojme.
     */
    'quality' => [
        'jpeg' => (int) env('IMAGES_JPEG_QUALITY', 82),
        'webp' => (int) env('IMAGES_WEBP_QUALITY', 80),
    ],

    /*
     * Poistka proti „decompression bomb" – GD drží raster nekomprimovaný,
     * 50 Mpx je zhruba 200 MB pamäte a nad tým už ide o útok, nie o fotku.
     */
    'max_pixels' => (int) env('IMAGES_MAX_PIXELS', 50000000),

    /*
     * Sťahovanie obrázka z cudzej adresy. Zoznam hostiteľov je tu preto, aby
     * sa cez URL z cudzieho API nedalo siahnuť na vnútornú sieť (SSRF).
     */
    'fetch' => [
        'timeout' => (int) env('IMAGES_FETCH_TIMEOUT', 10),
        'retries' => (int) env('IMAGES_FETCH_RETRIES', 2),
        'max_bytes' => (int) env('IMAGES_FETCH_MAX_BYTES', 15728640),
        'allowed_hosts' => [
            'i.ytimg.com',
            'i1.ytimg.com',
            'i2.ytimg.com',
            'i3.ytimg.com',
            'i4.ytimg.com',
            'i9.ytimg.com',
            'img.youtube.com',
            'yt3.ggpht.com',
        ],
    ],

    /*
     * Lokálny vývoj nemá súbory v úložisku, načítavajú sa z produkcie.
     * Predtým to bola podmienka priamo v modeli a platila len pre náhľady,
     * takže veľký obrázok sa lokálne nikdy nezobrazil.
     */
    'remote_base' => env(
        'IMAGES_REMOTE_BASE',
        env('APP_ENV') === 'local' ? 'https://hlascirkvi.sk/storage' : null
    ),

];
