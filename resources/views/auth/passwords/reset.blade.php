@extends('layouts.auth')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Nové heslo',
        'description' => 'Nastavte si nové heslo k účtu na portáli Hlas Cirkvi.',
        'noindex' => true,
    ];
@endphp

@section('heading', 'Nové heslo')
@section('subtitle', 'Zadajte heslo, ktorým sa budete prihlasovať odteraz.')

@section('card')
    {{-- Cieľ je password.update (POST /password/reset). Predtým tu stála
         route('password.request') - tá adresa sedí, ale je to názov GET
         formulára; pri zmene ciest by sa odosielanie ticho rozpadlo. --}}
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="ar-label" for="email">E-mailová adresa</label>
            <input id="email" name="email" type="email" autocomplete="email" inputmode="email"
                   value="{{ $email ?? old('email') }}" maxlength="100" required autofocus
                   class="ar-input @error('email') ar-input--bad @enderror">
            @error('email')<p class="ar-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ar-label" for="password">Nové heslo</label>
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
            Uložiť nové heslo
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('login') }}" class="ar-link font-semibold">Späť na prihlásenie</a>
@endsection
