# Administrácia a dashboard

Stránky správy kanála rozširujú `layouts.dashboard`, administrácia `layouts.admin`
(ten rozširuje `layouts.dashboard`). Spoločné štýly načítava `partials.dashboard-head`.

- `x-dashboard.frame`: rozloženie s navigáciou; `label` a `navigation-label` menia jej označenie, `fold` zbalí menu na mobile (formulárové stránky).
- `x-dashboard.header`: nadpis `heading`, sloty `lead` a `actions`.
- `x-dashboard.shell`: rám a hlavička kanála, povinný `canal`, voliteľný `heading`.
- `x-pages.dashboard`: rovnaký rám pre formuláre a detaily so slotmi `title`, `title_right`, `page`.
- `x-pages.admin`: rám administrácie so spoločnými filtrami a rovnakými slotmi.
- `x-dashboard.panel`: panel s voliteľným `title`, slotmi `note`, `footer`; `flush` vypína odsadenie obsahu pre riadkové výpisy a grafy.
- `x-dashboard.metric`: hodnota `value`, popis `label`, doplňujúci text v hlavnom slote.
- `x-dashboard.table`: posúvateľná tabuľka s prístupným názvom `label`; slot obsahuje `thead` a `tbody`.
- `x-dashboard.empty`: jednotná správa prázdneho výpisu.
- `x-dashboard.form-bar`: lepiaca lišta s tlačidlom uloženia (`submit`, `cancel`, `note`); musí byť vnútri `<form>`, po úprave poľa upozorní na neuložené zmeny.
- `x-dashboard.status-badge`: farebný štítok stavu, `badge` (pole `label`/`tone`/`title`, napr. `$user->accountBadge()`) alebo jednotlivé atribúty; tóny `green`, `amber`, `red`, `gray`.

Komponenty panel, metric, table, empty a frame prijímajú ďalšie HTML atribúty aj `class`.
Spoločný vzhľad panelov, metrík a lišty formulára je v `partials.dashboard-system`, formulárov a tabuliek
v `partials.admin-system`. Farby a navigácia používajú existujúci `partials.design-system`.
