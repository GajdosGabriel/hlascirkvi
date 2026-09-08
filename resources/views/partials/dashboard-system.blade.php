{{-- Stavebné prvky nástenky kanála. Stoja na palete `--ar-*` zo spoločného
     partialu (partials/design-system.blade.php), ale bývajú len tu: dlaždice
     s číslami, graf a riadkové výpisy nikde inde na webe nie sú a do
     spoločného súboru by len pribúdali. --}}
<style>
    /* ---- Panel -------------------------------------------------------- */

    /* Karta nástenky. Oproti .ar-card sa nedvíha pri prejdení myšou — panel
       nie je odkaz, len rám okolo obsahu. */
    .ar-panel {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--ar-line);
        border-radius: .625rem;
        background: #fff;
    }
    .ar-panel:has(.dropdown-slot-menu) { overflow: visible; }
    .ar-panel__head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 1rem;
        border-bottom: 1px solid var(--ar-line);
        padding: .7rem 1rem;
    }
    .ar-panel__title {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #9ca3af;
    }
    .ar-panel__note { font-size: .75rem; color: #b6bac3; }
    .ar-panel__body { padding: 1rem; }
    /* Výpisy siahajú po okraj panela, vlastné odsadenie nesú až riadky. */
    .ar-panel__body--flush { padding: 0; }
    .ar-panel__foot {
        margin-top: auto;
        border-top: 1px solid var(--ar-line);
        padding: .55rem 1rem;
        font-size: .75rem;
    }

    /* ---- Dlaždica s číslom -------------------------------------------- */

    .ar-kpi {
        display: flex;
        flex-direction: column;
        gap: .35rem;
        border: 1px solid var(--ar-line);
        border-radius: .625rem;
        background: #fff;
        padding: .9rem 1rem 1rem;
    }
    .ar-kpi__label {
        font-size: .7rem;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: #9ca3af;
    }
    .ar-kpi__value {
        font-family: Inter, system-ui, sans-serif;
        font-size: 1.75rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        letter-spacing: -.03em;
        line-height: 1.05;
    }
    .ar-kpi__note {
        font-size: .75rem;
        color: var(--ar-ink-soft);
    }

    /* Zmena proti predchádzajúcemu mesiacu. Farbí sa len smer, nie hodnota —
       pokles komentárov nie je chyba, ktorú treba zvýrazniť červenou ako
       poplach, preto tlmené odtiene. */
    .ar-delta {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 9999px;
        padding: .05rem .4rem;
        font-size: .6875rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }
    .ar-delta--up   { background: #ecfdf5; color: #047857; }
    .ar-delta--down { background: #fef2f2; color: #b91c1c; }
    .ar-delta--flat { background: var(--ar-paper-deep); color: #6b7280; }

    /* ---- Graf ---------------------------------------------------------- */

    /* Pod ~420 px by sa popisy osí zmenšili s celým SVG na nečitateľné
       a graf radšej posunieme, než aby sme ho nechali splesnúť. */
    .ar-chart__wrap { overflow-x: auto; }
    .ar-chart {
        display: block;
        width: 100%;
        min-width: 420px;
        height: auto;
    }
    .ar-chart__grid { stroke: var(--ar-line); stroke-width: 1; }
    .ar-chart__area { fill: url(#ar-chart-fill); }
    .ar-chart__line {
        fill: none;
        stroke: var(--ar-accent);
        stroke-width: 2;
        stroke-linejoin: round;
        stroke-linecap: round;
    }
    .ar-chart__dot { fill: var(--ar-accent); }
    .ar-chart__label { font-size: 12px; fill: #b6bac3; }

    /* Mesačný pásik pod grafom: koľko toho kanál vydal za posledný rok. */
    .ar-months {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: .3rem;
        align-items: end;
    }
    .ar-months__bar {
        display: block;
        border-radius: 2px 2px 0 0;
        background: rgba(var(--ar-accent-rgb), .75);
        transition: background-color .15s ease;
    }
    .ar-months__bar:hover { background: var(--ar-accent); }
    /* Mesiac bez príspevku musí byť vidieť ako prázdny, nie ako chýbajúci. */
    .ar-months__bar--empty { background: var(--ar-paper-deep); }
    .ar-months__label {
        margin-top: .3rem;
        font-size: .5625rem;
        text-align: center;
        color: #b6bac3;
    }

    /* ---- Riadkové výpisy ----------------------------------------------- */

    .ar-row {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        border-bottom: 1px solid var(--ar-line);
        padding: .6rem 1rem;
        transition: background-color .12s ease;
    }
    .ar-row:last-child { border-bottom: 0; }
    a.ar-row:hover { background: var(--ar-paper); }
    .ar-row__title {
        font-size: .8125rem;
        font-weight: 500;
        line-height: 1.35;
        color: var(--ar-ink);
    }
    .ar-row__meta {
        margin-top: .15rem;
        font-size: .6875rem;
        color: #9ca3af;
    }
    /* Číslo vpravo. tabular-nums drží stĺpec zarovnaný aj pri rôznych
       šírkach číslic. */
    .ar-row__value {
        flex: 0 0 auto;
        margin-left: auto;
        font-size: .8125rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: var(--ar-ink-soft);
        white-space: nowrap;
    }

    /* Prázdny panel. Bez neho vyzerá čerstvý kanál ako rozbitá stránka. */
    .ar-empty {
        padding: 1.5rem 1rem;
        text-align: center;
        font-size: .8125rem;
        color: #9ca3af;
    }

    /* ---- Upozornenie --------------------------------------------------- */

    .ar-alert {
        display: flex;
        gap: .7rem;
        border: 1px solid #fde68a;
        border-left-width: 3px;
        border-radius: .5rem;
        background: #fffbeb;
        padding: .8rem 1rem;
        font-size: .8125rem;
        color: #92400e;
    }

    /* ---- Riadok výpisu v správe ---------------------------------------- */

    /* Články, semináre a modlitby v správcovskej časti. Oproti .ar-row nesie
       náhľad a stĺpec akcií — výpis je pracovný zoznam, nie rebríček. */
    .ar-item {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        border-bottom: 1px solid var(--ar-line);
        padding: .75rem 1rem;
        transition: background-color .12s ease;
    }
    .ar-item:last-child { border-bottom: 0; }
    .ar-item:hover { background: var(--ar-paper); }

    .ar-item__thumb {
        position: relative;
        display: block;
        flex: 0 0 8rem;
        overflow: hidden;
        border-radius: .375rem;
        background: var(--ar-paper-deep);
        aspect-ratio: 16 / 9;
    }
    .ar-item__thumb img,
    .ar-item__thumb picture {
        display: block;
        height: 100%;
        width: 100%;
        object-fit: cover;
    }
    /* Dĺžka videa v rohu náhľadu, rovnako ako na verejných kartách. */
    .ar-item__time {
        position: absolute;
        right: .25rem;
        bottom: .25rem;
        border-radius: .25rem;
        background: rgba(0, 0, 0, .75);
        padding: .05rem .3rem;
        font-size: .625rem;
        font-variant-numeric: tabular-nums;
        color: #fff;
    }

    .ar-item__body { min-width: 0; flex: 1 1 auto; }
    .ar-item__title {
        display: block;
        font-family: Inter, system-ui, sans-serif;
        font-size: .875rem;
        font-weight: 600;
        line-height: 1.35;
        color: var(--ar-ink);
    }
    .ar-item__title:hover { color: var(--ar-accent); }
    .ar-item__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .15rem .75rem;
        margin-top: .3rem;
        font-size: .6875rem;
        color: #9ca3af;
    }
    .ar-item__meta i { margin-right: .2rem; }
    .ar-item__tags {
        display: flex;
        flex-wrap: wrap;
        gap: .3rem;
        margin-top: .4rem;
    }

    /* Stĺpec akcií. Tlmený, kým naň nejde myš — vo výpise s tridsiatimi
       riadkami by tridsať farebných tlačidiel prekrylo samotné články. */
    .ar-item__actions {
        display: flex;
        flex: 0 0 auto;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: .3rem;
    }
    .ar-act {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border: 1px solid transparent;
        border-radius: .375rem;
        background: none;
        padding: .25rem .5rem;
        font-size: .75rem;
        font-weight: 500;
        color: #9ca3af;
        cursor: pointer;
        transition: color .12s ease, border-color .12s ease, background-color .12s ease;
    }
    .ar-act:hover {
        border-color: var(--ar-line);
        background: #fff;
        color: var(--ar-ink);
    }
    .ar-act--danger:hover { border-color: #fecaca; color: #b91c1c; }
    .ar-act--ok:hover { border-color: #a7f3d0; color: #047857; }

    /* Pod ~640 px sa akcie zalomia pod obsah, inak by z riadku ostal na
       názov jeden stĺpec písmen. */
    @media (max-width: 640px) {
        .ar-item { flex-wrap: wrap; }
        .ar-item__thumb { flex-basis: 5.5rem; }
        .ar-item__actions { margin-left: auto; }
    }

    /* ---- Avatar kanála v hlavičke -------------------------------------- */

    /* Rovnaká skladba ako .ar-org__avatar vo výpise kanálov: iniciály ležia
       pod obrázkom, takže po chýbajúcom súbore ostane čitateľná skratka
       a nie rozbitý obrázok. Tu len väčší a okrúhly. */
    .ar-shell__avatar {
        position: relative;
        display: grid;
        flex: 0 0 3.5rem;
        width: 3.5rem;
        height: 3.5rem;
        place-items: center;
        overflow: hidden;
        border-radius: 9999px;
        background: var(--ar-accent-soft);
        font-family: Inter, system-ui, sans-serif;
        font-size: 1.125rem;
        font-weight: 800;
        color: var(--ar-accent);
        text-transform: uppercase;
    }
    .ar-shell__avatar img {
        position: absolute;
        inset: 0;
        height: 100%;
        width: 100%;
        object-fit: cover;
    }
</style>
