@extends('layouts.auth')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Registrácia',
        'description' => 'Vytvorte si účet na Hlas Cirkvi a komentujte, ukladajte si príspevky '
            . 'a sledujte novinky z kresťanských kanálov.',
        'noindex' => true,
    ];
@endphp

@section('heading', 'Vytvorte si účet')
@section('subtitle', 'Komentáre, obľúbené príspevky a novinky z kanálov, ktoré sledujete.')

@section('card')
    {{-- Registrácia cez Google stojí hore zámerne: je na jedno kliknutie
         a adresa z nej prichádza už overená, takže je to najkratšia cesta
         k hotovému účtu. --}}
    <a href="{{ route('auth.redirect', 'google') }}" class="ar-oauth" rel="nofollow">
        <svg class="ar-oauth__mark" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
            <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.62z"/>
            <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.84.86-3.04.86-2.34 0-4.32-1.58-5.03-3.7H.96v2.33A9 9 0 0 0 9 18z"/>
            <path fill="#FBBC05" d="M3.97 10.72a5.4 5.4 0 0 1 0-3.44V4.95H.96a9 9 0 0 0 0 8.1l3.01-2.33z"/>
            <path fill="#EA4335" d="M9 3.58c1.32 0 2.5.46 3.44 1.35l2.58-2.58C13.46.9 11.43 0 9 0A9 9 0 0 0 .96 4.95l3.01 2.33C4.68 5.16 6.66 3.58 9 3.58z"/>
        </svg>
        <span>Pokračovať cez Google</span>
    </a>

    <a href="{{ route('auth.redirect', 'facebook') }}" class="ar-oauth mt-3" rel="nofollow">
        <svg class="ar-oauth__mark" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
            <path fill="#1877F2" d="M18 9a9 9 0 1 0-10.4 8.9v-6.3H5.3V9h2.3V7c0-2.3 1.36-3.56 3.45-3.56.999 0 2.04.18 2.04.18v2.25h-1.15c-1.13 0-1.49.7-1.49 1.42V9h2.53l-.4 2.6h-2.13v6.3A9 9 0 0 0 18 9z"/>
        </svg>
        <span>Pokračovať cez Facebook</span>
    </a>

    <div class="ar-or">alebo e-mailom</div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <x-human-check/>

        {{-- Hlášky neviditeľnej kontroly (App\Support\HumanCheck) nemajú
             vlastné políčko, pri ktorom by sa dali vypísať. --}}
        @error(\App\Support\HumanCheck::STAMP)
            <div class="ar-note ar-note--bad">{{ $message }}</div>
        @enderror

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="ar-label" for="first_name">Meno</label>
                <input id="first_name" name="first_name" type="text" autocomplete="given-name"
                       value="{{ old('first_name') }}" maxlength="50" required autofocus
                       class="ar-input @error('first_name') ar-input--bad @enderror">
                @error('first_name')<p class="ar-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ar-label" for="last_name">Priezvisko</label>
                <input id="last_name" name="last_name" type="text" autocomplete="family-name"
                       value="{{ old('last_name') }}" maxlength="50" required
                       class="ar-input @error('last_name') ar-input--bad @enderror">
                @error('last_name')<p class="ar-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="ar-label" for="email">E-mailová adresa</label>
            <input id="email" name="email" type="email" autocomplete="email" inputmode="email"
                   value="{{ old('email') }}" maxlength="100" required
                   class="ar-input @error('email') ar-input--bad @enderror">
            @error('email')
                <p class="ar-error">{{ $message }}</p>
            @else
                <p class="ar-hint">Pošleme na ňu potvrdzovací odkaz.</p>
            @enderror
        </div>

        <div>
            <label class="ar-label" for="password">Heslo</label>
            <div class="ar-input-wrap">
                <input id="password" name="password" type="password" autocomplete="new-password" required
                       class="ar-input @error('password') ar-input--bad @enderror" style="padding-right:4.5rem">
                <button type="button" class="ar-reveal" data-reveal="password" aria-pressed="false">Zobraziť</button>
            </div>
            @error('password')
                <p class="ar-error">{{ $message }}</p>
            @else
                <p class="ar-hint">Najmenej 8 znakov. Heslá zo známych únikov neprijímame.</p>
            @enderror
        </div>

        <div>
            <label class="ar-label" for="password_confirmation">Heslo ešte raz</label>
            <div class="ar-input-wrap">
                <input id="password_confirmation" name="password_confirmation" type="password"
                       autocomplete="new-password" required class="ar-input" style="padding-right:4.5rem">
                <button type="button" class="ar-reveal" data-reveal="password_confirmation" aria-pressed="false">Zobraziť</button>
            </div>
        </div>

        <button type="submit" class="ar-btn ar-btn--accent w-full" style="padding:.7rem 1rem;font-size:.9rem">
            Zaregistrovať sa
        </button>

        <p class="text-center text-xs" style="color: var(--ar-ink-soft)">
            Registráciou súhlasíte so <a href="{{ route('gdpr') }}" class="ar-link">spracovaním osobných údajov</a>.
        </p>
    </form>
@endsection

@section('footer')
    Už máte účet? <a href="{{ route('login') }}" class="ar-link font-semibold">Prihláste sa</a>
@endsection
