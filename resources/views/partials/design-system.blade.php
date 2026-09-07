{{-- Spoločná paleta a stavebné prvky verejnej časti. Pôvodne žili priamo
     v layoute článku; odkedy rovnaký vzhľad nosí aj úvodná stránka, stoja
     na jednom mieste a oba layouty ich len vložia. Špecifiká článku
     (ar-prose, ar-player, ar-progress) ostávajú v layouts/article. --}}
<style>
    :root {
        --ar-paper:       #f6f6f7;
        --ar-paper-deep:  #ececee;
        --ar-ink:         #101828;
        --ar-ink-soft:    #545a67;
        --ar-line:        #e3e4e8;
        --ar-accent:      #b91c1c;
        --ar-accent-soft: #fdf1f1;
        /* Zložky akcentu zvlášť: mriežka archívu mieša jeho odtiene cez
           rgba(), a do tej sa hotová hex hodnota nedá vložiť. */
        --ar-accent-rgb:  185, 28, 28;
    }

    .ar-body {
        background-color: var(--ar-paper);
        color: var(--ar-ink);
        font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
    }

    /* Nadpisy a čísla — úzke, bezpätkové, s tesným prestrkom. */
    .ar-display {
        font-family: Inter, system-ui, -apple-system, sans-serif;
        letter-spacing: -.022em;
    }

    /* Nadradený štítok nad titulkom (názov kanála, sekcie). */
    .ar-kicker {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--ar-accent);
    }

    .ar-card {
        background: #fff;
        border: 1px solid var(--ar-line);
        transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
    }
    .ar-card:hover {
        border-color: #cfd2da;
        box-shadow: 0 18px 40px -30px rgba(16, 24, 40, .6);
        transform: translateY(-2px);
    }

    /* Náhrada obrázka pri príspevkoch bez fotky aj bez avatara kanála. */
    .ar-noimage {
        background: linear-gradient(135deg, var(--ar-paper-deep), #f7f7f8);
        color: #b3b7c0;
    }

    /* Náhľad karty. Pomer drží mriežku zarovnanú aj keď náhľady prídu
       v rôznych rozmeroch — a pri lazysizes rezervuje miesto ešte pred
       stiahnutím obrázka, takže mriežka pri načítaní neposkakuje. */
    .ar-thumb {
        display: block;
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        background: var(--ar-paper-deep);
        transition: transform .35s ease;
    }
    .ar-card:hover .ar-thumb { transform: scale(1.04); }

    /* Titulky v kartách musia končiť na pevnom počte riadkov, inak dlhý
       názov roztiahne jednu bunku mriežky nad ostatné. */
    .ar-clamp-2,
    .ar-clamp-3 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .ar-clamp-2 { -webkit-line-clamp: 2; }
    .ar-clamp-3 { -webkit-line-clamp: 3; }

    /* Prepínač výpisu (najnovšie / odporúčané / trend / najsledovanejšie). */
    .ar-tab {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border: 1px solid var(--ar-line);
        border-radius: 9999px;
        background: #fff;
        padding: .4rem .85rem;
        font-size: .8125rem;
        font-weight: 500;
        color: var(--ar-ink-soft);
        white-space: nowrap;
        transition: border-color .15s ease, color .15s ease, background-color .15s ease;
    }
    .ar-tab:hover {
        border-color: #cfd2da;
        color: var(--ar-ink);
    }
    .ar-tab--on {
        background: var(--ar-accent-soft);
        border-color: #f3c9c9;
        color: var(--ar-accent);
        font-weight: 600;
    }

    /* Hľadanie vedľa prepínača. Zabalené do vlastných tried, lebo šírka sa
       mení podľa fokusu — v Tailwinde by to bola zhluk arbitrárnych variantov.
       Trieda ar-search--open drží pole roztiahnuté, keď sa už hľadalo. */
    .ar-search {
        position: relative;
        flex: 0 0 auto;
    }
    .ar-search input {
        width: 9.5rem;
        border: 1px solid var(--ar-line);
        border-radius: 9999px;
        background: #fff;
        padding: .4rem 2.25rem .4rem .9rem;
        font-size: .8125rem;
        color: var(--ar-ink);
        outline: none;
        transition: width .28s ease, border-color .15s ease;
    }
    .ar-search input::placeholder { color: #9ca3af; }
    .ar-search input:focus,
    .ar-search--open input {
        width: 16rem;
        border-color: var(--ar-accent);
    }
    /* Na úzkych displejoch by roztiahnuté pole pretieklo z riadka. */
    @media (max-width: 480px) {
        .ar-search { flex: 1 1 100%; }
        .ar-search input,
        .ar-search input:focus,
        .ar-search--open input { width: 100%; }
    }
    .ar-search button {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        display: flex;
        width: 2.25rem;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        transition: color .15s ease;
    }
    .ar-search button:hover { color: var(--ar-accent); }

    /* ---- Rebríček v paneli "Naj z kanála" ------------------------------ */

    .ar-rank {
        flex: 0 0 1.25rem;
        font-family: Inter, system-ui, sans-serif;
        font-size: 1rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        line-height: 1.35;
        text-align: right;
        color: #c8ccd4;
    }
    .ar-rank--first { color: var(--ar-accent); }

    /* ---- Čísla kanála v hlavičke profilu -------------------------------- */

    .ar-stat { border-left: 2px solid var(--ar-line); padding-left: .7rem; }
    .ar-stat__value {
        display: block;
        font-family: Inter, system-ui, sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        letter-spacing: -.02em;
        line-height: 1.15;
    }
    .ar-stat__label {
        display: block;
        margin-top: .1rem;
        font-size: .7rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #9ca3af;
    }

    /* ---- Mriežka archívu ------------------------------------------------ */

    /* Rok na riadok, mesiac na stĺpec. Sýtosť políčka je podiel z najsilnejšieho
       mesiaca kanála, takže na jeden pohľad vidno, kedy sa v kanáli dialo
       najviac — a zároveň je to navigácia: políčko je odkaz na daný mesiac. */
    .ar-archive {
        display: grid;
        grid-template-columns: 2.35rem repeat(12, 1fr);
        gap: 2px;
        align-items: center;
    }
    .ar-archive__head {
        font-size: .55rem;
        font-weight: 600;
        text-align: center;
        color: #b6bac3;
    }
    .ar-archive__year {
        font-size: .7rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: var(--ar-ink-soft);
        transition: color .15s ease;
    }
    .ar-archive__year:hover { color: var(--ar-accent); }
    .ar-archive__year.is-on { color: var(--ar-accent); }

    .ar-month {
        display: block;
        aspect-ratio: 1 / 1;
        border-radius: 3px;
        background: var(--ar-paper-deep);
        transition: transform .12s ease, box-shadow .12s ease;
    }
    /* Mesiac bez príspevku ostáva prázdny, nesmie sa dať kliknúť. */
    .ar-month--empty { opacity: .55; }
    a.ar-month:hover {
        transform: scale(1.22);
        box-shadow: 0 0 0 1px #fff, 0 0 0 2px var(--ar-ink);
    }
    .ar-month.is-on { box-shadow: 0 0 0 1px #fff, 0 0 0 2px var(--ar-ink); }

    /* ---- Drobnosti ---------------------------------------------------- */

    .ar-rule { display: flex; align-items: center; gap: 1rem; }
    .ar-rule::after { content: ""; flex: 1; height: 1px; background: var(--ar-line); }

    .ar-link {
        background-image: linear-gradient(currentColor, currentColor);
        background-size: 0 1px;
        background-repeat: no-repeat;
        background-position: 0 100%;
        transition: background-size .25s ease;
    }
    .ar-link:hover { background-size: 100% 1px; }

    /* ---- Bočný panel --------------------------------------------------- */

    /* Panely vpravo skladajú Vue komponenty a x-cards.card, ktoré nosia
       triedy .card a .card_header z app.css — teda modrú hlavičku pôvodného
       vzhľadu. Prepíšeme ich len v rámci .ar-aside, aby administrácia
       a ostatné stránky ostali nedotknuté. */
    .ar-aside .card {
        margin-bottom: 1rem;
        overflow: hidden;
        border: 1px solid var(--ar-line);
        border-radius: .5rem;
        background: #fff;
    }
    .ar-aside .card:last-child { margin-bottom: 0; }

    .ar-aside .card_header {
        padding: .7rem 1rem;
        border-radius: 0;
        border-bottom: 1px solid var(--ar-line);
        background: #fff;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #9ca3af;
    }
    .ar-aside .card_header h4 { font: inherit; letter-spacing: inherit; }
    .ar-aside .card_header svg,
    .ar-aside .card_header i { color: var(--ar-accent); }

    .ar-aside .card_body { padding: .85rem 1rem; }
    .ar-aside .card > ul { padding: .5rem; }
    .ar-aside .card > ul > li,
    .ar-aside .card > ul > a > li { border-radius: .375rem; }
</style>
