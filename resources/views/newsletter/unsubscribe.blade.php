@extends('layouts.auth')

@php
    $seo = [
        'title' => 'Odhlásenie z odberu noviniek',
        'description' => 'Odhlásenie z odberu noviniek portálu Hlas Cirkvi.',
        'noindex' => true,
    ];
@endphp

@section('card')
    <div class="text-center">
        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full"
             style="background: var(--ar-accent-soft); color: var(--ar-accent)">
            <i class="far fa-envelope text-xl"></i>
        </div>

        @if ($subscribed)
            <h1 class="ar-display mt-4 text-2xl font-bold">Odhlásiť odber noviniek?</h1>

            <p class="mt-3 text-sm" style="color: var(--ar-ink-soft)">
                Mesačný prehľad noviniek vám už nebudeme posielať. E-maily týkajúce sa
                vášho účtu (napríklad obnova hesla) budú chodiť ďalej.
            </p>

            <form method="POST" action="{{ $action }}" class="mt-6">
                @csrf
                <button type="submit" class="ar-btn ar-btn--accent w-full" style="padding:.65rem 1rem">
                    Áno, odhlásiť odber
                </button>
            </form>
        @else
            <h1 class="ar-display mt-4 text-2xl font-bold">Odber je zrušený</h1>

            <p class="mt-3 text-sm" style="color: var(--ar-ink-soft)">
                Novinky vám už posielať nebudeme. Ak si to rozmyslíte, stačí napísať
                administrátorovi portálu.
            </p>
        @endif

        <a href="{{ route('posts.index') }}" class="ar-btn ar-btn--quiet mt-3 w-full" style="padding:.65rem 1rem">
            Pokračovať na portál
        </a>
    </div>
@endsection
