# Portréty kanálov 359–361

Stiahnuté a vizuálne overené 2. 10. 2026. Originálne formáty a fotografie sú zachované.

| Kanál | Osoba | Zdrojová stránka | Súbor |
| --- | --- | --- | --- |
| 359 | František Trstenský | https://kbs.sk/obsah/sekcia/h/hladat/p/rimskokatolicka-cirkev/c/mons-frantisek-trstensky | https://www.kbs.sk/upload/trstensky_kbs.gif |
| 360 | Mário Tomášik | https://e-n-c.org/council/ | https://e-n-c.org/wp-content/uploads/2020/03/ENC-Council_Mario-Tomasik_SK-1024x1024-800x800.png |
| 361 | Michal Zamkovský | https://redemptoristi.sk/knaza-spoznas-v-spovednici-rozhovor-s-p-michalom-zamkovskym/ | https://redemptoristi.sk/wp-content/uploads/2018/03/o.-Michal.jpg |

Zdrojové stránky neuvádzajú otvorenú licenciu týchto fotografií; uvedenie zdroja samo osebe nepredstavuje licenciu.

Migrácia `2026_10_02_100000_fill_named_canal_portraits.php` nahráva súbory cez `images.disk` (na produkcii S3) do `organizations/{id}/portrait-2026-10-02.{ext}`. Vyžaduje zhodné ID aj meno, organizačný režim, nezmazaný kanál a prázdny avatar. Existujúce avatary zostávajú zachované. Pri chybe nahrávania sa zápis do databázy nevykoná; opakovanie migrácie preskočí už doplnené kanály.

Nasadenie: priložiť migráciu aj tento adresár a spustiť bežné `php artisan migrate --force`. Migrácia nesťahuje nič z internetu.
