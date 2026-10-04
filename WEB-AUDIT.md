# Kontrola webu cez prehliadač – 4. októbra 2026

Kontrolované lokálne na http://hlascirkvi.local, v existujúcej prihlásenej relácii. Prejdené: úvodná stránka, vyhľadávanie, Trend, stránkovanie, vzdelávanie, podujatia, modlitebný múr, nedeľné prenosy, detail videa a profil kanála. Mobilný náhľad aj bežná šírka prehliadača.

## Opravené

- Mobilná úvodná stránka pretekala do šírky 583 px pri dostupných 375 px. Výpis a bočný panel teraz povoľujú zmenšenie v mriežke; overená šírka stránky 375 px.
- Prepnutie poradia zahadzovalo vyhľadávanie a odoslanie vyhľadávania zahadzovalo poradie. Obe hodnoty sa teraz zachovávajú, prepnutie poradia začína prvou stranou.
- Prázdne výsledky majú vysvetlenie a priamy návrat na najnovšie príspevky.
- Stránkovanie malo názvy odkazov pagination.goto_page a doslovné HTML entity v prístupných popisoch. Doplnené preklady a správne znaky šípok.
- Ikony Facebook, WhatsApp a e-mail na detaile majú zrozumiteľné názvy pre čítačky obrazovky. Pomenované aj tlačidlo vyhľadávania.

## Overenie

- Vyhľadávanie Ježiš + Najsledovanejšie: obe hodnoty zachované aj po prechode na stranu 2.
- Prázdny výsledok: viditeľný návrat na najnovšie príspevky.
- Tri odkazy zdieľania dostupné pod novými názvami.
- Zostavenie frontendových súborov úspešné.
- PostShowTest, PostTrendsFilterTest, PostUrlTest: 7 testov, 30 kontrol, bez zlyhania. Testovací nástroj hlási upozornenia na zastarané používanie.
- Náhľad opraveného mobilu: storage/app/audit-mobile-after.jpg.

## Ďalšie návrhy a nálezy na preverenie

- Pôvodných 161 kariet vo vzdelávaní bolo v pokračovaní nahradených štyrmi ukážkami z každej série. Vyhľadávanie a výber roku zostávajú návrhom na rozšírenie.
- Archív CB Hermanovce ukazuje dva záznamy s názvom 20.9.2026 - Laci Mižík - Božia výzbroj. Pred zlúčením treba porovnať zdrojové videá, môžu byť odlišné.
- Podujatie Misia v Ríme má na karte adresu rímskeho kostola, ale obec Bratislava. Treba preveriť zdrojové dáta portálu podujatí; nesprávna obec ovplyvňuje aj filter.
- Na lokálnej úvodnej stránke sú testovacie komentáre a modlitba z predchádzajúceho testovania. Zvážiť oddelenie testovacích dát od bežného obsahu.

Predchádzajúca časť opisuje prvé kolo verejnej kontroly. Nasleduje rozšírená kontrola zabezpečenej zóny a zostávajúcich sekcií.
## Pokračovanie: zabezpečená zóna a celý rozsah sekcií

Prejdené všetky sekcie z hlavného menu, profilu a administrácie, doplnené stránky známe z registrovaných trás. Pri dynamických zoznamoch boli kontrolované reprezentatívne detaily, formuláre a filtre. Nešlo o otvorenie každého zo 42-tisíc príspevkov ani každej modlitby.

| Oblasť | Kontrolované stránky a toky |
| --- | --- |
| Verejný obsah | Úvod, vyhľadávanie, poradie, stránkovanie, kanál a archív, detail príspevku, zdieľanie, osobnosti, nedeľné prenosy |
| Vzdelávanie | Rozcestník, verejný detail seminára, prednášky a odkazy na ich detail |
| Podujatia | Detail, najbližšie, víkend, prebiehajúce, archív, plagáty, mapa, prázdne vyhľadávanie a zachovanie filtrov |
| Čitateľ | Modlitby, uložené články, odber noviniek, upozornenia, čítania a navigácia medzi dňami, zamyslenia a navigácia |
| Správa kanála | Nástenka, kanály, detail, založenie a úprava kanála, články a formuláre, semináre a formuláre, modlitby a formuláre |
| Administrácia | Úvod, používatelia a ich detail/úprava, filtre vrátane zrušených účtov, kanály a založenie, články, modlitby a filtre, komentáre, oznamy a založenie, predný zoznam, AI, štatistika, buffer, denník, kôš obrázkov |
| Vstup a chybové stavy | Prihlásenie, registrácia a požiadanie o obnovu hesla v dočasnom anonymnom náhľade rovnakých šablón; neexistujúca stránka 404 |
| Mobil | Verejné a osobné stránky, detail seminára aj jeho správa, kanál a jeho úprava, prihlasovacie formuláre, nástenka správcu, zoznamy článkov/kanálov, administrátorské zoznamy používateľov, predný zoznam a buffer |

### Ďalšie opravy

- Verejný aj správcovský detail seminára obsahoval nezaregistrovaný komponent card-front: prednášky zostávali neviditeľné. Karty sa teraz vykresľujú na serveri; v kontrolovanom seminári je všetkých 10 prednášok.
- Správa seminárov odkazovala na neexistujúce adresy úpravy a mazania. Zdieľaná ponuka používa správne trasy v kanáli a oprávnenia na serveri.
- Predchádzajúca klientská kontrola oprávnenia vracala pole, ktoré bolo vždy pravdivé. Správcovské formuláre sa zobrazujú len oprávneným používateľom.
- Import playlistu bol odkaz GET na trasu povolenú len pre POST. Nahradil ho formulár POST s ochranou CSRF a potvrdením.
- Prepínač zverejnenia seminára má skutočné tlačidlo, formulár a aktualizovaný stav po uložení. Nezverejnený seminár má verejne stav 404; jeho správca ho môže prezerať.
- Starý odkaz na prednášku v seminári zobrazoval dashboard. Teraz overí príslušnosť príspevku a presmeruje na verejný detail.
- Vzdelávanie zobrazuje štyri ukážky každej série a odkaz na všetky prednášky. Počet 161 ostal zachovaný; kontrolovaný rozcestník má 36 kariet v 9 sériách.
- Súhrn kanálov bez správcu ignoroval zrušené účty v zozname správcov: nástenka uvádzala 2, výpis 192. Teraz oba používajú rovnakú definíciu a nástenka ukazuje 192.
- Súhrn komentárov zahŕňal importované komentáre, kým výpis ukazoval overených používateľov portálu. Obe miesta teraz používajú spoločný výber. Zavedená nová verzia cache súhrnov.
- Tlačidlá Späť a Zrušiť vo formulári článku vedú na správu článkov, nezávisia od náhodne predchádzajúcej stránky.
- Prázdny administrátorský výber modlitieb má vysvetlenie a návrat na celý zoznam.
- Archív zrušených modlitieb a modlitieb zo zrušených kanálov neponúka nefunkčnú úpravu/mazanie. Zrušený používateľ má odkaz na čitateľný detail, bez nefunkčnej úpravy.
- Opravený slovenský formát dátumu vytvorenia modlitby, názvy používateľov a buffera, anonymné meno a spojenie popisov s poľami modlitby.
- Rádiá majú skutočné odkazy otvorené v novej karte a ochranu pred prístupom k pôvodnému oknu. Odstránené ich globálne štýly, ktoré zasahovali zoznamy na celom webe. Prehrávanie všetkých externých staníc nebolo overené.
- Podujatia majú hlavný nadpis aj bez vybraného odporúčaného podujatia a pri prázdnom výbere.
- Zamyslenie ukazuje dátum zvoleného záznamu v slovenčine. Počet dní sa počíta kalendárne, vrátane zmeny času. Opravené označenie biblických veršov a názvy predchádzajúceho/nasledujúceho zamyslenia.
- Chybové stránky majú jeden platný odkaz na úvod namiesto tlačidla vnoreného do odkazu a návratu, ktorý mohol návštevníka opäť zaviesť na chybu.
- Úvod ochrany údajov pomenúva portál Hlas Cirkvi a kontakt vedie na existujúci formulár v pätičke.

### Overenie a hranice

- Pôvodné overenie: 497 testov, 7 504 kontrol, bez zlyhania. Pri 408 testoch vzniklo rovnaké upozornenie na zastaranú konštantu `PDO::MYSQL_ATTR_SSL_CA` v spoločnej konfigurácii databázy; 89 prešlo bez upozornenia. Nešlo o 408 zastaraných testov.
- Konfigurácia teraz používa `Pdo\Mysql::ATTR_SSL_CA` od PHP 8.4 a pôvodnú konštantu len na podporovanom PHP 8.3. Opätovné overenie 4. 10. 2026: všetkých 500 aktuálnych testov prešlo, 7 543 kontrol, bez upozornení na zastarané používanie (zapnuté `--fail-on-deprecation` a `--fail-on-phpunit-deprecation`).
- Nové regresné testy overujú viditeľné karty, správne odkazy a POST importu, zákaz správy cudzieho seminára, súkromie nezverejneného seminára, obmedzenie ukážok pre každú sériu, súhrny komentárov a zrušených správcov, archívne odkazy a dátumy zamyslení.
- Zostavenie frontendových súborov úspešné. Verejné semináre, správcovský detail a opravené formuláre boli otvorené v prehliadači.
- Pri uvedených mobilných kontrolách mala stránka 375 px pri dostupnej šírke 375 px.
- Posledná doplnková dávka mobilných kontrol všetkých administrátorských stránok prerušila spojenie s prehliadačom. Opätovné spojenie a reset zlyhali; túto dávku preto nepovažujem za dokončenú.
- Správy, reálne publikovanie, import z YouTube, zmeny účtov, vymazanie a odber noviniek neboli vykonané na existujúcich dátach. Správanie a oprávnenia sa overovali izolovanými testami; reálne doručovanie e-mailov nebolo overené.
- Anonymné náhľady boli po kontrole odstránené. Existujúce prihlásenie nebolo zámerne odhlásené.
- Zmeny sú lokálne, bez nasadenia alebo commitu. Predchádzajúce rozpracované zmeny boli zachované.

### Zostávajúce obsahové nálezy a návrhy

- Úplný text ochrany údajov obsahuje historické podmienky iného e-shopu, účtovníctvo a ďalšie vyhlásenia nezodpovedajúce jednoznačne portálu. Úvod bol opravený; celé znenie potrebuje potvrdenie skutočných postupov prevádzkovateľa a odbornú revíziu.
- Nesprávna obec podujatia v Ríme pochádza zo zdroja podujatí. Bez opravy zdroja nemožno spoľahlivo meniť lokálne filtrovanie alebo polohu podľa názvu.
- Podobné názvy videí v archíve nie sú samy osebe dôkazom duplicity. Automatické zlučovanie nebolo vykonané.
- Testovacie články, komentáre a modlitba z predchádzajúceho testovania zostali zachované.
- Vzdelávanie môže dostať vyhľadávanie a filter roku. Výber kanála pri článku môže mať vyhľadávanie namiesto dlhého zoznamu.

## Dokončenie uvedených obsahových nálezov – 4. 10. 2026

- Rímske miesto opravené v lokálnom zdrojovom projekte podujatí vrátane obce, krajiny, PSČ, popisu a súradníc. Verejný zdroj vyžaduje nasadenie migrácie.
- Podozrenie na duplicitu vyriešené porovnaním YouTube metadát: jedno video malo zastaraný názov. Názov a metadáta sú opravené, obe odlišné videá zostali zachované.
- Dva testovacie články, tri komentáre a jedna modlitba archivované s možnosťou obnovy.
- Doplnené hľadanie vo vzdelávaní, filter ročníka a vyhľadávanie kanála pri článku.
- Podrobný rozsah, overenie a postup nasadenia: deploy/audit-followup-2026-10-04.md.

## Záverečné doplnenie kontroly – 4. 10. 2026

Dokončená mobilná kontrola všetkých hlavných administrátorských sekcií a doplnkových formulárov. Opravená chyba filtrov administrácie seminárov a pretekanie upozornenia o vypnutom importe v zozname kanálov. Doplnené regresné testy. Aktuálna šablóna ochrany údajov už obsahuje prepracovaný text pre portál; odborné potvrdenie prevádzkových údajov ostáva potrebné. Štyri prehrávače rádií sa spustili; Lumen Gospel sa nespustil na externej stránke. Zmeny zostávajú lokálne. Podrobnosti a posledné kroky: deploy/audit-completion-2026-10-04.md.
