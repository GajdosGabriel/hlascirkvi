{{-- Spoločný rám prihlasovacích stránok (prihlásenie, registrácia, zabudnuté
     heslo, overenie e-mailu). Karta, políčka aj tlačidlá poskytovateľov stáli
     predtým v každom pohľade zvlášť - a každý vyzeral inak. Pohľad si teraz
     dopĺňa len obsah karty, prípadne nadpis a pätičku pod ňou. --}}
@extends('layouts.app')

@section('body-class', 'ar-body')

{{-- Chyby vypisujeme pri jednotlivých políčkach, súhrnný zoznam
     z layouts/errors by ich zopakoval nad kartou. --}}
@section('own-errors', true)

@section('content')
<div class="mx-auto w-full max-w-md px-4 py-10 sm:py-16">

    {{-- Nadpis karty je zámerne `heading`, nie `title`: sekciu `title` číta
         partials/meta ako hotovú značku <title> (staršia konvencia), takže
         obyčajný text v nej by stránku nechal bez titulku. Titulok pre
         vyhľadávače si pohľad nastaví poľom $seo. --}}
    @hasSection('heading')
        <div class="text-center">
            <p class="ar-kicker">HlasCirkvi.sk</p>
            <h1 class="ar-display mt-2 text-3xl font-bold">@yield('heading')</h1>

            @hasSection('subtitle')
                <p class="mt-2 text-sm" style="color: var(--ar-ink-soft)">@yield('subtitle')</p>
            @endif
        </div>
    @endif

    <div class="ar-card @hasSection('heading') mt-8 @endif rounded-xl p-6 sm:p-8"
         style="box-shadow: 0 24px 60px -40px rgba(16,24,40,.55)">
        @yield('card')
    </div>

    @hasSection('footer')
        <p class="mt-6 text-center text-sm" style="color: var(--ar-ink-soft)">
            @yield('footer')
        </p>
    @endif
</div>
@endsection

@section('headerCSS')
<style>
    .ar-label {
        display: block;
        margin-bottom: .35rem;
        font-size: .8125rem;
        font-weight: 600;
        color: var(--ar-ink);
    }

    .ar-input {
        width: 100%;
        padding: .6rem .75rem;
        border: 1px solid var(--ar-line);
        border-radius: .5rem;
        background: #fff;
        color: var(--ar-ink);
        font-size: .9375rem;
        line-height: 1.4;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ar-input:focus {
        outline: none;
        border-color: var(--ar-ink);
        box-shadow: 0 0 0 3px rgba(var(--ar-accent-rgb), .12);
    }
    .ar-input--bad { border-color: var(--ar-accent); }

    .ar-input-wrap { position: relative; }
    .ar-reveal {
        position: absolute;
        top: 50%;
        right: .5rem;
        transform: translateY(-50%);
        padding: .2rem .4rem;
        font-size: .75rem;
        font-weight: 600;
        color: var(--ar-ink-soft);
    }
    .ar-reveal:hover { color: var(--ar-accent); }

    .ar-hint { margin-top: .3rem; font-size: .75rem; color: var(--ar-ink-soft); }
    .ar-error { margin-top: .3rem; font-size: .75rem; font-weight: 600; color: var(--ar-accent); }

    /* Zvýraznená hláška nad formulárom - odoslaný odkaz, zlyhaná kontrola. */
    .ar-note {
        border: 1px solid var(--ar-line);
        border-radius: .5rem;
        padding: .75rem;
        font-size: .8125rem;
        line-height: 1.45;
    }
    .ar-note--bad {
        border-color: var(--ar-accent);
        background: var(--ar-accent-soft);
        color: var(--ar-accent);
    }
    .ar-note--ok {
        border-color: #bbe5c5;
        background: #f1faf3;
        color: #17693a;
    }

    /* Zapamätanie prihlásenia. Vlastná odrážka, lebo pôvodná sa v Safari
       nedá zafarbiť na akcent. */
    .ar-check {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .8125rem;
        color: var(--ar-ink-soft);
        cursor: pointer;
    }
    .ar-check input {
        width: .95rem;
        height: .95rem;
        accent-color: var(--ar-accent);
    }

    /* Tlačidlá poskytovateľov. Biely podklad a tenký rámik sú požiadavkou
       značkových pravidiel Googlu - farebné G nesmie stáť na farbe. */
    .ar-oauth {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .6rem;
        width: 100%;
        padding: .65rem 1rem;
        border: 1px solid var(--ar-line);
        border-radius: .5rem;
        background: #fff;
        color: var(--ar-ink);
        font-size: .9375rem;
        font-weight: 600;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ar-oauth:hover {
        border-color: #cfd2da;
        box-shadow: 0 10px 24px -18px rgba(16,24,40,.7);
        color: var(--ar-ink);
    }
    .ar-oauth__mark { width: 1.15rem; height: 1.15rem; flex: 0 0 auto; }

    .ar-or {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin: 1.5rem 0;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--ar-ink-soft);
    }
    .ar-or::before,
    .ar-or::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--ar-line);
    }
</style>
@yield('authCSS')
@endsection

@section('script')
<script>
    // Prepínač viditeľnosti hesla. Bez neho sa dlhšie heslo píše naslepo
    // dvakrát a človek zbytočne končí na hláške, že sa heslá nezhodujú.
    //
    // Posluchač visí na dokumente, nie na samotných tlačidlách: Vue montuje
    // #app až po tomto skripte (app.js nesie Vite ako modul, teda odložene)
    // a pritom obsah prekreslí. Tlačidlám, ktoré tu nájdeme teraz, by posluchač
    // v tej chvíli zanikol spolu s nimi a prepínač by nerobil nič.
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-reveal]');

        if (! button) {
            return;
        }

        var field = document.getElementById(button.dataset.reveal);
        var shown = field.type === 'text';

        field.type = shown ? 'password' : 'text';
        button.textContent = shown ? 'Zobraziť' : 'Skryť';
        button.setAttribute('aria-pressed', shown ? 'false' : 'true');
    });
</script>
@endsection
