@extends('layouts.auth')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Potvrdenie e-mailovej adresy',
        'description' => 'Potvrďte e-mailovú adresu svojho účtu na portáli Hlas Cirkvi.',
        'noindex' => true,
    ];
@endphp

@section('card')
    <div class="text-center">
        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full"
             style="background: var(--ar-accent-soft); color: var(--ar-accent)">
            <i class="far fa-envelope text-xl"></i>
        </div>

        <h1 class="ar-display mt-4 text-2xl font-bold">Pozrite si schránku</h1>

        <p class="mt-3 text-sm" style="color: var(--ar-ink-soft)">
            Potvrdzovací odkaz sme poslali na adresu
            <span class="font-semibold" style="color: var(--ar-ink)">{{ auth()->user()->email }}</span>.
            Kliknutím naň potvrdíte, že adresa patrí vám.
        </p>

        <p class="mt-3 text-sm" style="color: var(--ar-ink-soft)">
            Na portál sa medzitým dostanete aj bez potvrdenia — bez neho vám ale
            nemôžeme posielať novinky a upozornenia.
        </p>

        <div class="mt-6 flex flex-col items-center gap-3">
            <a href="{{ route('posts.index') }}" class="ar-btn ar-btn--accent w-full"
               style="padding:.65rem 1rem">Pokračovať na portál</a>

            <form method="POST" action="{{ route('verification.resend') }}" class="w-full">
                @csrf
                <button type="submit" class="ar-btn ar-btn--quiet w-full" style="padding:.65rem 1rem">
                    Poslať e-mail znova
                </button>
            </form>
        </div>

        <p class="mt-5 text-xs" style="color: var(--ar-ink-soft)">
            E-mail neprišiel? Skúste priečinok s nevyžiadanou poštou. Odkaz platí 7 dní.
        </p>
    </div>
@endsection
