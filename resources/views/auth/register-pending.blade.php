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
            sme poslali potvrdzovací odkaz. Kliknutím overíte svoju adresu
            a sprístupníte účet. Dovtedy zostávate neprihlásený.
        </p>

        <div class="mt-6 flex flex-col items-center gap-3">
            @if ($pending->send_count < \App\Models\PendingRegistration::MAX_SENDS)
            <form method="POST" action="{{ route('register.resend') }}" class="w-full">
                @csrf
                <button type="submit" class="ar-btn ar-btn--quiet w-full" style="padding:.65rem 1rem">
                    Poslať e-mail znova
                </button>
            </form>
            <p class="text-xs" style="color: var(--ar-ink-soft)">Ďalší e-mail možno poslať najskôr po 2 minútach. Platí vždy najnovší odkaz.</p>
            @else
            <p role="status" class="ar-note">Dosiahli ste limit odoslaní. Použite posledný e-mail s odkazom alebo sa po vypršaní platnosti registrujte znova.</p>
            @endif

            <a href="{{ route('register') }}" class="text-sm" style="color: var(--ar-ink-soft)">
                Preklep v adrese? Vyplniť formulár znova
            </a>
            <a href="{{ route('login') }}" class="text-sm" style="color: var(--ar-ink-soft)">Už mám potvrdený e-mail — prihlásiť sa</a>
        </div>

        <p class="mt-5 text-xs" style="color: var(--ar-ink-soft)">
            E-mail neprišiel? Skúste priečinok s nevyžiadanou poštou.
            Odkaz platí do {{ $pending->expires_at->format('d.m.Y H:i') }}.
        </p>
    </div>
@endsection
