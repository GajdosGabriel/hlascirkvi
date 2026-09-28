# Dopĺňanie kontaktov kanálov

Po nasadení tejto úpravy nastavte v produkčnom `.env`:

```dotenv
OPENAI_ENRICHMENT_MODEL=gpt-6-luna
```

Toto nastavenie je samostatné; `OPENAI_SUMMARY_MODEL` a `OPENAI_REPLY_MODEL` ho nemenia.

Spustite novú migráciu a obnovte konfiguráciu:

```sh
php artisan migrate --force
php artisan config:cache
```

Opakované preverenie oboch nahlásených kanálov:

```sh
php artisan canals:enrich --retry-empty --canal=745 --canal=595 --limit=2
```

Všetkých desať nahlásených kanálov:

```sh
php artisan canals:enrich --retry-empty --canal=758 --canal=755 --canal=745 --canal=661 --canal=658 --canal=650 --canal=649 --canal=648 --canal=642 --canal=595 --limit=10
```

Opakovanie sa týka len dokončených záznamov bez uložených zmien. Existujúce údaje kanála sa neprepisujú. Vypínač dopĺňania a spoločný mesačný rozpočet zostávajú v platnosti. Príkaz vypíše aktuálne nakonfigurovaný model; administrácia ukazuje aj model jednotlivých nových hľadaní a dôvody zamietnutia.

Nové prázdne výsledky sa automaticky opakujú po šiestich hodinách, najviac do troch pokusov. Jeden pokus obsahuje najviac dve volania modelu; druhé sa vykoná iba pri chýbajúcich údajoch a nevyčerpanom rozpočte. Vyhľadávanie je dôkladnejšie a môže spotrebovať viac prostriedkov než pôvodný jeden krátky prieskum.

Pri lokálnom overení cez skutočné API sa našiel web, e-mail aj telefón pre oba uvedené názvy. Produkčné profily počas overenia neboli upravené. Testovacie odpovede AI nie sú zárukou rovnakého výsledku každého ďalšieho vyhľadávania.
