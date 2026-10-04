# Dokončenie kontroly – 4. októbra 2026

## Opravy z tejto kontroly

- Administrácia seminárov vracala TypeError: do `keep` komponentu filtrov sa omylom odovzdala definícia výberu namiesto názvov parametrov. Odstránená nadbytočná definícia; stav zverejnenia naďalej spravuje `selects`.
- Zoznam kanálov na mobile pretekal kvôli nezalamovanému upozorneniu o zastavenom YouTube importe. Odznaky v podrobnostiach kanála teraz zalamujú text aj dlhé adresy.
- AdminRoutesTest teraz otvára aj zoznam seminárov a formulár nového seminára. SeminarPagesTest overuje kombináciu hľadania a zverejnenia, výsledné záznamy a zachovanie filtrov vo formulároch.

## Prehliadač

Kontrola v existujúcej prihlásenej relácii na http://hlascirkvi.local. Mobilný viewport 375 × 812; dostupná šírka dokumentu je 360 px kvôli 15 px zvislému posuvníku. Po opravách šírka obsahu zodpovedá dostupnej šírke.

Otvorené všetky hlavné sekcie administrácie: úvod, používatelia, kanály, články, semináre, modlitby, komentáre, oznamy, predný zoznam, AI, štatistika, buffer a denník. Doplnené formuláre nového seminára, kanála a oznamu, kôš obrázkov a ochrana údajov. Žiadna z týchto stránok po opravách nepretekala ani nevrátila chybovú stránku. Neznamená to otvorenie každého detailu a každej kombinácie filtrov.

Hľadanie Hanusove + zverejnené semináre zachovalo oba parametre a zobrazilo zodpovedajúce série.

## Rádiá

- Rádio 7 SK: stránka presmeruje na HTTPS; po spustení sa načítavanie zmenilo na ovládanie pauzy. Prehrávač nevystavuje HTML audio element, takže stav dekódovania nebol meraný.
- Rádio 7 CZ: stream načítaný, readyState 4, prehrávanie aktívne a čas postúpil nad 16 sekúnd.
- Rádio Lumen: stream https://audio.lumen.sk/live64.mp3 načítaný, readyState 3, prehrávanie aktívne.
- Rádio Mária: stream načítaný, readyState 4, prehrávanie aktívne.
- Lumen Gospel: stránka sa otvorí, ale stream sa po kliknutí nespustil. Opakované pokusy ukázali prázdny zdroj/about:blank alebo ff128.mp3 bez načítaných dát. Ide o problém reprodukovaný priamo na stránke poskytovateľa; odkaz zostal zachovaný. Pred uzavretím nálezu preveriť službu u Lumenu.

Tieto kontroly overujú stav prehrávača, nie subjektívne počúvanie zvuku.

## Funkčné overenie

Úplná sada pred doplnením regresného testu: 504 testov, 7 573 kontrol, bez deprecation upozornení. Cielená sada po oprave: 10 testov, 73 kontrol. Konečný úplný beh po opravách: 505 testov, 7 581 kontrol, bez upozornení. Výsledok je v storage/app/audit-completion-tests.txt a XML v storage/app/audit-completion-tests.xml. Frontendové zostavenie úspešné.

Publikovanie, účty, mazanie, odber a import sú pokryté izolovanými testami nad samostatnou databázou *_test. YouTube odpovede aj odosielanie správ sú v testoch simulované. Táto kontrola neposielala skutočné správy, nemenila odber existujúcich používateľov a nespustila hromadný import na bežnej databáze.

## Ochrana údajov

Aktuálna šablóna už obsahuje celý prepracovaný text pre portál, nie historické podmienky e-shopu. Staršia časť WEB-AUDIT.md preto nepopisuje aktuálnu šablónu. Overené otvorenie stránky a mobilná šírka; text v tejto kontrole nebol právne schválený.

Prevádzkovateľ má ešte potvrdiť:

- identitu a kontaktné údaje;
- skutočných poskytovateľov hostingu, pošty a záloh, ich prístup k údajom a lehoty záloh;
- reálne vykonávanie dennej údržby na produkcii (expirácie, IP adresy, denník, zobrazenia);
- dobu uchovávania vybavených správ a spôsob výmazu/anonymizácie účtu a verejného obsahu;
- právne základy, citlivé údaje v modlitbách, AI spracúvanie a záruky prenosov mimo EHP;
- spôsob získania súhlasu pri existujúcich odberateľoch noviniek.

Konečné znenie potrebuje odbornú revíziu s týmito skutočnými údajmi. Samotná kontrola kódu nepotvrdí prevádzkové postupy a zmluvy.

## Nasadenie a posledné overenie

Zmeny zostávajú lokálne. Pracovný adresár obsahuje aj rozsiahle skoršie rozpracované zmeny, ktoré zostali zachované. Tento beh nevytvoril commit ani nenasadil produkciu.

1. Skontrolovať a uložiť finálny rozsah zmien vrátane nových súborov a public/build.
2. Pred nasadením zálohovať produkčné databázy oboch projektov.
3. Nasadiť Hlas Cirkvi podľa deploy/deploy.sh: závislosti, migrácie, obnova cache a queue worker. Obsahové migrácie a poznámky: deploy/audit-followup-2026-10-04.md a deploy/hanus-days-seminars-2026-10-04.md.
4. Samostatne nasadiť migráciu rímskeho miesta do zdrojového portálu podujatí. Kópia migrácie je deploy/2026_10_04_150000_correct_rome_venue.php; nepatrí do databázy Hlas Cirkvi.
5. Po oprave verejného portálu obnoviť cache podujatí Hlas Cirkvi; pri výpadku zdroja môže stará záložná odpoveď zostať až 7 dní.
6. Na produkcii overiť semináre, filtre, mobilné kanály a rímske miesto; overiť bežiaci plánovač a frontu.
7. S konkrétnou schválenou skúšobnou adresou overiť doručenie správy, obnovy hesla a newslettera vrátane odhlasovania. Odoslanie cez mailer nie je dôkaz doručenia do schránky.