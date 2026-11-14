@extends('layouts.auth')

@php
    $seo = [
        'title' => 'Vypočutá modlitba',
        'description' => 'Potvrdenie vypočutej modlitby na portáli Hlas Cirkvi.',
        'noindex' => true,
    ];
@endphp

{{-- Odkaz z e-mailu PrayerFulfilledOrNotYet. Označí až tlačidlo (POST),
     samotné otvorenie odkazu nič nemení. --}}
@section('card')
    <div class="text-center">
        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full"
             style="background: var(--ar-accent-soft); color: var(--ar-accent)">
            <i class="ph ph-hands-praying text-xl"></i>
        </div>

        @if ($prayer->fulfilled_at === null)
            <h1 class="ar-display mt-4 text-2xl font-bold">Bola modlitba vypočutá?</h1>
        @else
            <h1 class="ar-display mt-4 text-2xl font-bold">Modlitba je medzi vypočutými</h1>
        @endif

        @if (filled($prayer->title))
            <p class="mt-3 text-sm font-semibold">„{{ $prayer->title }}“</p>
        @endif

        @if ($prayer->fulfilled_at === null)
            <p class="mt-3 text-sm" style="color: var(--ar-ink-soft)">
                Po potvrdení ju zaradíme medzi vypočuté modlitby — môže povzbudiť aj ostatných.
            </p>

            <form method="POST" action="{{ $action }}" class="mt-6">
                @csrf
                <button type="submit" class="ar-btn ar-btn--accent w-full" style="padding:.65rem 1rem">
                    Áno, modlitba bola vypočutá
                </button>
            </form>
        @else
            <p class="mt-3 text-sm" style="color: var(--ar-ink-soft)">
                Označená ako vypočutá {{ $prayer->fulfilled_at->format('j. n. Y') }}. Ďakujeme.
            </p>
        @endif

        <a href="{{ route('modlitby.index') }}" class="ar-btn ar-btn--quiet mt-3 w-full" style="padding:.65rem 1rem">
            Pokračovať na modlitby
        </a>
    </div>
@endsection
