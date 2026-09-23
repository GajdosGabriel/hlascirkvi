@extends('layouts.auth')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Dokončenie registrácie',
        'description' => 'Potvrďte e-mailovú adresu a dokončite registráciu na portáli Hlas Cirkvi.',
        'noindex' => true,
    ];
@endphp

@section('card')
    <div class="text-center">
        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full"
             style="background: var(--ar-accent-soft); color: var(--ar-accent)">
            <i class="far fa-envelope text-xl"></i>
        </div>

        <h1 class="ar-display mt-4 text-2xl font-bold">Ešte jeden krok</h1>

        <p class="mt-3 text-sm" style="color: var(--ar-ink-soft)">
            Na adresu
            <span class="font-semibold" style="color: var(--ar-ink)">{{ $email }}</span>
            sme poslali odkaz. Účet vytvoríme, až keď naň kliknete — tak vieme,
            že adresa naozaj patrí vám.
        </p>

        <div class="mt-6 flex flex-col items-center gap-3">
            <form method="POST" action="{{ route('register.resend') }}" class="w-full">
                @csrf
                <button type="submit" class="ar-btn ar-btn--quiet w-full" style="padding:.65rem 1rem">
                    Poslať e-mail znova
                </button>
            </form>

            <a href="{{ route('register') }}" class="text-sm" style="color: var(--ar-ink-soft)">
                Preklep v adrese? Vyplniť formulár znova
            </a>
        </div>

        <p class="mt-5 text-xs" style="color: var(--ar-ink-soft)">
            E-mail neprišiel? Skúste priečinok s nevyžiadanou poštou.
            Odkaz platí {{ \App\Models\PendingRegistration::TTL_DAYS }} dní, potom sa nepotvrdená registrácia zmaže.
        </p>
    </div>
@endsection
