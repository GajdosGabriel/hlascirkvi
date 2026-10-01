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
    @include('auth.partials.google-button', ['context' => 'signup'])

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
                <p class="ar-hint">Pošleme na ňu odkaz — účet vznikne až po jeho potvrdení.</p>
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
