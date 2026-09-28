# Overené profily kanálov — 28. 9. 2026

Preverených bolo 59 nezmazaných kanálov označených ako organizácia. Dávka obsahuje 42 profilov, 41 nových opisov (každý presne 10 viet v 3 odsekoch) a 9 dvojíc súradníc. TKKBS už opis má, preto dostáva len chýbajúce kontakty. Nie každý profil má verejne overiteľný telefón alebo e-mail; také údaje sa nevymýšľajú.

## Nasadenie

Preniesť oba nižšie uvedené migračné súbory aj `database/data/canal-profiles-2026-09-28.json`. Pred spustením urobiť zálohu produkčnej databázy. V koreňovom adresári produkčnej aplikácie spustiť:

```sh
php artisan migrate --force --path=database/migrations/2026_09_28_100000_add_canal_coordinates.php --path=database/migrations/2026_09_28_110000_fill_verified_canal_profiles.php
```

Tieto konkrétne cesty nespúšťajú ostatné rozpracované migrácie. Produkcia nebola v rámci tejto úlohy nasadená. Ak už boli tieto migrácie spustené, Laravel ich znovu nespustí.

Dátová migrácia vyžaduje zhodu ID, slugu, názvu a typu organizácie; zmazané alebo odlišné záznamy preskočí. Produkčné ID musia zodpovedať lokálnym. Dopĺňa len prázdne hodnoty a zachováva existujúci opis aj kontakty. Súradnice doplní iba ako úplnú dvojicu pri zhode ulice a obce. Existujúce neprázdne zástupné hodnoty ako „Neuvedené“ neprepisuje. Po nasadení skontrolovať počet reálne doplnených záznamov; pri odlišných produkčných údajoch môže byť nižší.

Nevolá externé API, nevyužíva platené obohacovanie a neposiela oznámenia. Opakované vykonanie dátovej časti nemení už doplnené záznamy. Dátová migrácia je zámerne jednosmerná: jej `down()` údaje nemaže, aby neodstránila neskoršie redakčné úpravy. Obnova obsahových údajov vyžaduje zálohu. Návrat schémy odstráni stĺpce latitude a longitude.

## Zdroje a súradnice

Každý profil v JSON obsahuje odkazy na zdroje a dátum overenia. Súradnice Family Garden pochádzajú z oficiálnej kontaktnej stránky. Ostatné polohy sú overené podľa konkrétnych budov v OpenStreetMap; ich zdrojové odkazy a atribúcia sú súčasťou dát. Pri zobrazovaní týchto mapových údajov zachovať atribúciu © OpenStreetMap contributors a odkaz https://www.openstreetmap.org/copyright (ODbL).

Centrum pre rodinu Trenčín má súradnice stáleho sídla na Farskej 12; opis uvádza dočasné pôsobisko počas rekonštrukcie. Pri kanáloch s neurčenou obcou sa súradnice nedopĺňajú. Nové stĺpce sú uložené v databáze; táto dávka nemení formuláre ani mapové zobrazenie webu.

## Zistené rozpory ponechané na redakčné rozhodnutie

- KS Milosť (256): existujúca Lazovná 77 sa líši od oficiálnej adresy Lazovná 72.
- KS Milosť Poprad (264): existujúce pole webu obsahuje identifikátor YouTube playlistu. Migrácia ho neprepisuje.
- Združenie Zázračnej medaily (649): databázová obec Bratislava sa nezhoduje s kontaktným sídlom v Nitre; ulica a súradnice neboli doplnené.
- Slovo života (260): uložená Tomášikova 30 verzus oficiálna 30B; existujúca adresa ostáva zachovaná.

## Kanály bez zmien

| ID | Kanál | Dôvod |
|---|---|---|
| 85 | Ján Krstiteľ | Existujúci opis; nejednoznačná väzba na konkrétne centrum Koinonie. |
| 100 | Neuvedený | Zástupný záznam bez určiteľnej organizácie. |
| 102 | ECAV | Kontakty a opis sú už vyplnené; poloha nebola samostatne overená. |
| 257 | Kresťanská Misia Maranata | Nepotvrdený aktuálny kontakt konkrétneho prevádzkovateľa kanála. |
| 263 | Rómsky zbor - Sabinov | Existujúci opis; chýba spoľahlivé potvrdenie aktuálnych kontaktov. |
| 266 | Apoštolská cirkev Bratislava | Nejednoznačné prepojenie všeobecného názvu a kontaktov s konkrétnym zborom. |
| 268 | Katolícka večerná univerzita | Relácia/playlist; neoverené priradenie organizačných kontaktov. |
| 272 | Voľné | Zástupný záznam. |
| 274 | Spoločenstvo Dobrého pastiera | Viac rovnomenných spoločenstiev, nepotvrdená identita. |
| 279 | Komunita redemptoristov a laikov (Koral) | Nepotvrdené aktuálne kontakty komunity. |
| 280 | KM Maranata Anglicko | Nepotvrdený aktuálny prevádzkovateľ a kontakty v Sheffielde. |
| 374 | Tomáš Halík | Osoba označená ako organizácia. |
| 383 | Jakub Limr | Osoba označená ako organizácia. |
| 385 | Matúš Marcin | Osoba označená ako organizácia. |
| 393 | Robert Balek | Osoba označená ako organizácia. |
| 501 | Viliam Judák | Osoba označená ako organizácia. |
| 758 | Fatima TV | Nepodarilo sa spoľahlivo overiť prevádzkovateľa slovenského kanála. |

## Overenie

Automatické testy kontrolujú 10 viet a 3 odseky, prítomnosť zdrojov, rozsahy súradníc, opakovateľnosť a zachovanie existujúcich údajov, odlišnej identity, obce, ulice aj čiastočne vyplnených súradníc. Výsledok: 3 testy, 719 kontrol bez zlyhania; existujúce upozornenie PHP 8.5 na zastaranú konštantu PDO v konfigurácii databázy.

Lokálne boli obe migrácie úspešne vykonané nad databázou na localhost. Porovnanie so zálohou potvrdilo 42 zmenených kanálov, 41 doplnených opisov, 87 kontaktných polí a 9 dvojíc súradníc. Lokálna záloha pôvodných 42 záznamov je v ignorovanom súbore `storage/app/canal-profiles-before-2026-09-28.json`; nie je súčasťou nasadenia ani náhradou produkčnej zálohy.
