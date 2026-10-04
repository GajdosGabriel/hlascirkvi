# Dokončenie obsahových opráv a filtrov – 4. 10. 2026

Zmeny sú lokálne, bez commitu a nasadenia.

## Hlas Cirkvi

- Vzdelávanie vyhľadáva názov a popis série, kanál a verejne dostupné prednášky. Pri zhode iba prednášky ukáže zodpovedajúce karty. Zachováva limit štyroch ukážok aj celkové počty.
- Ročník sa odvodzuje z jednoznačného roku v názve série. Rok importu sa nepoužíva. Séria bez ročníka má samostatnú voľbu.
- Výber kanála článku podporuje hľadanie bez diakritiky. Zachováva aktuálny výber a používa pôvodné oprávnenia: superadmin všetky nezmazané kanály, admin svoje kanály.
- Migrácia 2026_10_04_150000_archive_known_audit_fixtures.php archivuje presne dva články, tri komentáre a jednu modlitbu identifikované celým testovacím textom a časom vytvorenia. Obsah zostáva obnoviteľný cez deleted_at; iné záznamy so slovom test sa nemenia.
- Migrácia 2026_10_04_160000_correct_hermanovce_video_metadata.php opravuje názov, slug, trvanie a dátum YouTube pri videu bK0bgT-DIds. YouTube API 4. 10. 2026 potvrdilo názov 27.9.2026 - Marek Jurčo - Cirkev ako dar a trvanie 1:26:26. Video TZtc0Ea0iqc je iný záznam; obe videá a ich väzby zostávajú zachované. Starý slug presmeruje na nový.
- Obe migrácie boli použité v lokálnej databáze Hlas Cirkvi. Frontend bol zostavený.

## Zdroj podujatí

Oprava je v C:/www/event/api/database/migrations/2026_10_04_150000_correct_rome_venue.php, jej kópia je vedľa tohto návodu. Regresný test je v zdrojovom projekte v tests/Feature/Imports/RomeVenueCorrectionTest.php.

Lokálne bola migrácia použitá. Miesto kostol-san-girolamo-della-carita má obec Rím, krajinu Taliansko, Via di Monserrato 62/A, PSČ 00186 a súradnice 41.8953618, 12.4702096. Nová obec má region_id a district_id 0, preto nepatrí slovenskému kraju ani okresu. Oprava miesta platí pre oba naviazané podujatia (lokálne 5723 a 11047); podujatia sa neidentifikujú podľa názvu.

Zdroje: https://www.tkkbs.sk/view.php?cisloclanku=20260929023 a https://www.turismoroma.it/it/node/745.

Hlas Cirkvi naďalej používa verejný EVENT_PORTAL_URL=https://event.hlascirkvi.sk. Preto treba zdrojovú migráciu nasadiť aj na portál podujatí; lokálna oprava databázy event sa sama neprenesie na verejný server. Po nasadení obnoviť cache podujatí Hlas Cirkvi alebo počkať na TTL (zoznam 10 minút, detail 30 minút, obce 1 hodina). Pri výpadku zdroja môže klient použiť záložné staršie dáta až 7 dní.

## Overenie

- SeminarPagesTest a AuditFixtureCleanupTest: 8 testov, 55 kontrol.
- ArchiveVideoMetadataCorrectionTest: zachovanie oboch videí, správne metadáta vrátane časového pásma, opakovaný beh a ochrana ručne upraveného názvu.
- PostUrlTest: staré adresovanie ostáva podporované.
- PostCanalSelectTest: výber dostane len oprávnené kanály a zachová kanál článku, aj keď aktívny kanál používateľa je iný.
- Zdrojový RomeVenueCorrectionTest: 1 test, 9 kontrol; obec, krajina, PSČ, súradnice, idempotencia, nedotknuté iné miesto a dohľadanie Ríma v číselníku.
- Prehliadač: hoaxy + ročník 2021 ukazuje príslušnú prednášku; zrušenie filtrov funguje. Hľadanie halko nájde Haľko Jozef; neexistujúci názov nezmení vybraný kanál. Starý odkaz na video 42894 presmeruje na správny názov a nový slug.
- Zostavenie frontendových súborov prešlo.
