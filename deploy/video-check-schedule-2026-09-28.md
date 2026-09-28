# Kontroly videí a predný zoznam — 28. 9. 2026

## Správanie

- Vo formulári kanála sa „Deň kontroly videí“ zobrazí iba pri vyplnenom YouTube kanáli. Pri zmene poľa sa viditeľnosť mení okamžite; skryté pole sa neodosiela a zachová doterajšie nastavenie.
- S YouTube zdrojom prázdny deň znamená denný import (vrátane pôvodných nedeľných kontrol), konkrétny deň týždenný import o 16:24. Platí pre kanál aj jeho pripojený playlist. Automatická voľba pri uložení vyberie najmenej obsadený deň, uloží ho a ďalej ho svojvoľne nemení.
- Pôvodné hľadanie mena pri záznamoch bez YouTube zdroja ostáva zachované podľa existujúceho dňa o 6:55. Nastavenie vo formulári nie je zobrazené, podľa požiadavky na viditeľnosť len s YouTube kanálom.
- Zameškané týždenné kontroly ostávajú splatné. Po chybe sa skúšajú nasledujúci deň; úspešná kontrola sa neopakuje v ten istý deň. Ručný import s --canal zámerne obchádza týždenný rozvrh.
- Hľadanie mena používa najnovšie výsledky, stránkovanie a pevné časové okno. Prvý beh zahŕňa 30 dní; ďalšie nadväzujú na dokončené okno s dvojdňovým prekrytím. Rozpočet je 40 stránok na beh a najviac 3 na kanál. Nedokončená stránkovaná kontrola pokračuje nasledujúci deň; existujúce videá sa nezdvojujú. Parametre sú v config/youtube.php. Vyhľadávací index YouTube nemusí obsahovať všetky videá okamžite.
- Formulár zobrazuje posledný úspech, ďalší termín alebo čakanie na dobehnutie, rozpracované hľadanie a bezpečný text chyby.
- Superadmin mení členstvo v prednom zozname priamo vo formulári. Ostatným rolám zostáva stav iba na čítanie; podvrhnuté pole sa neuloží. Zmena vyprázdni cache predného zoznamu.

## Nasadenie

Spustiť novú migráciu pred obnovením plánovača a webových požiadaviek s novým kódom:

```sh
php artisan migrate --path=database/migrations/2026_09_28_130000_add_video_check_schedule_to_canals.php --force
```

Migrácia zachová existujúce import_day a inicializuje termíny rozložené podľa týchto dní. Pozor na zámernú zmenu významu: existujúci import_day pri vyplnenom YouTube zdroji, ktorý sa doteraz ignoroval, teraz riadi týždennú kontrolu tohto zdroja. Denný režim sa nastaví voľbou „Denne“. Pre kanály s živými prenosmi je vhodný denný režim.

Pri nasadení obnoviť konfiguračnú/view cache podľa existujúceho postupu projektu. Zmeny nepotrebujú nový frontendový build. Migrácia neprenáša historické úspechy, takže formulár spočiatku uvádza „Zatiaľ neevidovaná“.

## Overenie

Cielené testy: VideoImportScheduleTest, CanalVideoSettingsTest, CanalPropertiesTest, VideoSearchByNameScopeTest, YoutubeVideoImportTest, YoutubeImportDisableTest, FrontListTest. API sa v testoch simuluje; nesťahujú sa reálne videá.

Parametre hľadania: https://developers.google.com/youtube/v3/docs/search/list
