{{-- Prvky úvodu administrácie (admins/home) a výpisu komentárov
     (admins/comments). Stoja v hlavičke, nie v šablóne — <style nonce="{{ csp_nonce() }}"> vnútri
     #app Vue zahodí. --}}
<style nonce="{{ csp_nonce() }}">
    /* ---- Liturgický deň v hlavičke ------------------------------------- */
    .ar-liturgy {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .35rem .75rem;
        border: 1px solid var(--ar-line);
        border-left: 4px solid var(--ar-liturgy, var(--ar-line));
        border-radius: .625rem;
        background: #fff;
        padding: .7rem 1rem;
        font-size: .8125rem;
        color: var(--ar-ink-soft);
    }
    .ar-liturgy__title { font-weight: 700; color: var(--ar-ink); }
    .ar-liturgy__dot {
        width: .6rem;
        height: .6rem;
        flex: none;
        border-radius: 9999px;
        background: var(--ar-liturgy);
        box-shadow: 0 0 0 1px rgba(0, 0, 0, .08);
    }

    /* ---- Na čo sa pozrieť ---------------------------------------------- */
    .ar-todo { display: grid; gap: .5rem; }
    @media (min-width: 640px) { .ar-todo { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1280px) { .ar-todo { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .ar-todo__item {
        display: flex;
        align-items: center;
        gap: .7rem;
        border: 1px solid var(--ar-line);
        border-radius: .5rem;
        background: #fff;
        padding: .6rem .8rem;
        font-size: .8125rem;
        color: var(--ar-ink);
        transition: border-color .15s ease;
    }
    a.ar-todo__item:hover { border-color: var(--ar-accent); }
    .ar-todo__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        flex: none;
        border-radius: .5rem;
        background: #fffbeb;
        color: #b45309;
    }
    .ar-todo__item--info .ar-todo__icon { background: var(--ar-paper-deep); color: var(--ar-ink-soft); }
    .ar-todo__count { margin-left: auto; font-weight: 800; font-variant-numeric: tabular-nums; }

    /* ---- Mapa „kedy sa číta“ ------------------------------------------- */
    .ar-heat__wrap { overflow-x: auto; }
    .ar-heat {
        display: grid;
        grid-template-columns: 1.75rem repeat(24, minmax(.85rem, 1fr));
        gap: 2px;
        min-width: 26rem;
        align-items: center;
    }
    .ar-heat__cell {
        display: block;
        aspect-ratio: 1;
        border-radius: 2px;
        background: rgba(var(--ar-accent-rgb), var(--a, 0));
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .03);
    }
    .ar-heat__cell--zero { background: var(--ar-paper); }
    .ar-heat__day, .ar-heat__hour {
        font-size: .75rem;
        font-weight: 600;
        color: var(--ar-muted);
    }
    .ar-heat__hour { text-align: center; }
    .ar-heat__pub {
        display: block;
        align-self: end;
        border-radius: 1px 1px 0 0;
        background: var(--ar-ink-soft);
        opacity: .55;
    }
    .ar-heat__legend {
        display: flex;
        align-items: center;
        gap: .35rem;
        font-size: .75rem;
        color: var(--ar-muted);
    }
    .ar-heat__legend .ar-heat__cell { width: .7rem; }

    /* ---- Podiel (zaradenie kanálov, obľúbené) --------------------------- */
    .ar-split {
        display: flex;
        overflow: hidden;
        height: .6rem;
        border-radius: 9999px;
        background: var(--ar-paper-deep);
    }
    .ar-split > span { display: block; height: 100%; }
    .ar-split__legend {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem 1rem;
        margin-top: .6rem;
        font-size: .75rem;
        color: var(--ar-ink-soft);
    }
    .ar-split__legend i {
        display: inline-block;
        width: .55rem;
        height: .55rem;
        margin-right: .35rem;
        border-radius: 2px;
    }

    /* ---- Zaujímavosti -------------------------------------------------- */
    .ar-fact { display: flex; gap: .75rem; padding: .7rem 1rem; border-bottom: 1px solid var(--ar-line); }
    .ar-fact:last-child { border-bottom: 0; }
    .ar-fact__icon { flex: 0 0 1.25rem; padding-top: .1rem; text-align: center; color: var(--ar-accent); }
    .ar-fact__text { font-size: .8125rem; line-height: 1.45; color: var(--ar-ink-soft); }
    .ar-fact__text strong { color: var(--ar-ink); font-variant-numeric: tabular-nums; }

    .ar-avatar {
        width: 1.75rem;
        height: 1.75rem;
        flex: none;
        border-radius: 9999px;
        background: var(--ar-paper-deep);
        object-fit: cover;
    }

    /* ---- Výpis komentárov v administrácii ------------------------------ */
    .ar-comment__post {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        max-width: 100%;
        border-radius: .375rem;
        background: var(--ar-paper);
        padding: .3rem .55rem;
        font-size: .75rem;
        font-weight: 600;
        color: var(--ar-ink);
    }
    .ar-comment__post:hover { color: var(--ar-accent); }
    .ar-comment__post span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ar-comment__body {
        margin-top: .4rem;
        white-space: pre-line;
        overflow-wrap: anywhere;
        font-size: .8125rem;
        line-height: 1.5;
        color: #374151;
    }
    .ar-comment__quote {
        margin-top: .4rem;
        border-left: 2px solid var(--ar-line);
        padding-left: .6rem;
        font-size: .75rem;
        color: var(--ar-muted);
    }
    /* Hodnoty heatmapy ako tabuľka (admins/home) — pre dotyk a čítačky. */
    .ar-heat__table { margin-top: .75rem; font-size: .75rem; }
    .ar-heat__table summary { cursor: pointer; color: var(--ar-ink-soft); }
    .ar-heat__table summary:hover { color: var(--ar-accent); }
    .ar-heat__table table { margin-top: .5rem; width: 100%; border-collapse: collapse; font-variant-numeric: tabular-nums; }
    .ar-heat__table th, .ar-heat__table td { border-bottom: 1px solid var(--ar-line); padding: .3rem .4rem; text-align: right; white-space: nowrap; }
    .ar-heat__table thead th { font-weight: 600; color: var(--ar-muted); }
    .ar-heat__table tbody th { text-align: left; font-weight: 600; }

    /* Obnovenie čísel v kickeri úvodu. Podčiarknutý červený text na červenom
       kickeri bol drobný a splýval. */
    .ar-refresh {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        margin-left: .5rem;
        border: 1px solid var(--ar-line);
        border-radius: 9999px;
        background: #fff;
        padding: .1rem .55rem;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: none;
        color: var(--ar-ink-soft);
    }
    .ar-refresh:hover { border-color: var(--ar-accent); color: var(--ar-accent); }

    /* Odznak zdroja YouTube pri komentároch. */
    .ar-badge--youtube { background: #fef2f2; color: #b91c1c; }
    /* ---- Buffer -------------------------------------------------------- */
    .ar-plan { display: flex; flex-wrap: wrap; gap: .4rem; }
    .ar-plan__slot {
        border: 1px solid var(--ar-line);
        border-radius: 9999px;
        background: #fff;
        padding: .2rem .65rem;
        font-size: .8125rem;
        font-variant-numeric: tabular-nums;
        color: var(--ar-ink-soft);
    }
    .ar-plan__slot--done { border-color: #a7f3d0; background: #ecfdf5; color: #047857; }
    .ar-plan__slot--next { border-color: var(--ar-accent); background: var(--ar-accent); font-weight: 700; color: #fff; }
    .ar-buffer-canals { max-height: 32rem; overflow-y: auto; }
    .ar-row--on { background: var(--ar-accent-soft); }
    .ar-row--on .ar-row__title { font-weight: 600; color: var(--ar-accent); }
    /* Zbalené podrobnosti prepínačov na stránke AI zhrnutí. */
    .ar-ai__more { margin: -.25rem 0 .75rem 1.5rem; font-size: .75rem; color: var(--ar-muted); }
    .ar-ai__more summary { cursor: pointer; color: var(--ar-ink-soft); }
</style>
