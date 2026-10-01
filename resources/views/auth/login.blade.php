@extends('layouts.auth')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Prihlásenie',
        'description' => 'Prihláste sa do svojho účtu na portáli Hlas Cirkvi.',
        'noindex' => true,
    ];
@endphp

@section('heading', 'Vitajte späť')
@section('subtitle', 'Prihláste sa a pokračujte tam, kde ste skončili.')

@section('card')
    {{-- Prihlásenie cez poskytovateľa stojí hore zámerne: je na jedno
         kliknutie a nevyžaduje pamätať si ďalšie heslo. --}}
    @include('auth.partials.google-button', ['context' => 'signin'])

    <div class="ar-or">alebo e-mailom</div>

    {{-- Formulár odosiela prehliadač, nie Vue. Predtým tu bol axios volaný
         z LoginForm.vue: hlášky si skladal sám, na chybu odpovedal
         location.reload() a vzhľad sa s ostatnými stránkami rozchádzal.
         AuthenticatesUsers vráti chyby aj obmedzenie počtu pokusov sám. --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="ar-label" for="email">E-mailová adresa</label>
            <input id="email" name="email" type="email" autocomplete="email" inputmode="email"
                   value="{{ old('email') }}" maxlength="100" required autofocus
                   class="ar-input @error('email') ar-input--bad @enderror">
            @error('email')<p class="ar-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ar-label" for="password">Heslo</label>
            <div class="ar-input-wrap">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       class="ar-input @error('password') ar-input--bad @enderror" style="padding-right:4.5rem">
                <button type="button" class="ar-reveal" data-reveal="password" aria-pressed="false">Zobraziť</button>
            </div>
            @error('password')<p class="ar-error">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-between gap-3">
            <label class="ar-check" for="remember">
                <input id="remember" type="checkbox" name="remember" {{ old('remember', true) ? 'checked' : '' }}>
                <span>Zostať prihlásený</span>
            </label>

            <a href="{{ route('password.request') }}" class="ar-link text-sm" style="color: var(--ar-ink-soft)">
                Zabudnuté heslo?
            </a>
        </div>

        <button type="submit" class="ar-btn ar-btn--accent w-full" style="padding:.7rem 1rem;font-size:.9rem">
            Prihlásiť sa
        </button>
    </form>
@endsection

@section('footer')
    Nemáte účet? <a href="{{ route('register') }}" class="ar-link font-semibold">Zaregistrujte sa</a>
@endsection
