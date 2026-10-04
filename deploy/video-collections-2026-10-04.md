# Nepovinné kolekcie videí — 4. 10. 2026

Videá môžu zostať bez kolekcie alebo byť v niekoľkých kolekciách. Správa je v položke **Kolekcie a semináre** v menu kanála a administrácie.

## Používanie

1. Vytvorte kolekciu a zvoľte **Tematická kolekcia** alebo **Seminár / podujatie**.
2. Vyplňte názov, prípadne popis a odkaz na YouTube playlist. Nová kolekcia je koncept.
3. V správe vyhľadajte príspevky a označené pridajte alebo odoberte. Príspevky sa pri odobratí ani zmazaní kolekcie nemažú.
4. Príslušnosť možno meniť aj pri úprave príspevku pomocou nepovinných políčok. Vyhľadávanie kolekcií zachováva výber. Zmena kanála vyčistí výber z pôvodného kanála.
5. Tlačidlo **Načítať videá z YouTube** priradí videá z playlistu; opakovaný import zachová iné kolekcie. Zmazané videá neobnovuje. Nové tematické videá prechádzajú bežným publikovaním kanála.
6. Zverejnite kolekciu. Zobrazí sa na kanáli a pri zaradených príspevkoch. Semináre sa navyše zobrazujú v archíve konferencií a pútí.

Verejný obsah je stránkovaný; nezverejnené, zmazané a nedostupné videá sa v kolekcii nezobrazujú. Koncept kolekcie môžu prezerať jej správcovia. Import môže pripojiť aj už existujúce verejné video iného kanála bez zmeny jeho vlastníka. Ručne sa pridávajú príspevky vlastného kanála. Historické väzby medzi kanálmi sa pri bežnej úprave príspevku zachovávajú; odoberajú sa zo správy príslušnej kolekcie.

## Nasadenie

Existujúca tabuľka `seminars` a väzba `post_seminar` sa používajú aj pre kolekcie. Nový stĺpec `kind` má pre staré záznamy predvolenú hodnotu `seminar`. Existujúce ID, adresy a priradenia zostávajú platné. Triedy a názvy trás zostávajú kvôli kompatibilite `Seminar` / `seminars`.

Pred sprístupnením nového kódu spustiť migráciu:

```sh
php artisan migrate --path=database/migrations/2026_10_04_170000_extend_seminars_to_collections.php --force
php artisan view:clear
```

Nasadiť aj aktuálny `public/build` a jeho manifest (nový výber kolekcií vo Vue). Pri používaní cache trás ju obnoviť štandardným postupom nasadenia. Pre tento balík netreba spúšťať iné rozpracované dátové migrácie.

Lokálna migrácia aj zostavenie frontendu boli vykonané. Produkčné nasadenie nie je súčasťou tejto lokálnej úpravy.
