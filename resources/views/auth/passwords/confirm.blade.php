@extends('layouts.auth')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Potvrdenie hesla',
        'description' => 'Pre pokračovanie potvrďte svoje heslo.',
        'noindex' => true,
    ];
@endphp

@section('heading', 'Potvrďte heslo')
@section('subtitle', 'Než budete pokračovať, overte prosím, že pri zariadení stojíte vy.')

@section('card')
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label class="ar-label" for="password">Heslo</label>
            <div class="ar-input-wrap">
                <input id="password" name="password" type="password" autocomplete="current-password" required autofocus
                       class="ar-input @error('password') ar-input--bad @enderror" style="padding-right:4.5rem">
                <button type="button" class="ar-reveal" data-reveal="password" aria-pressed="false">Zobraziť</button>
            </div>
            @error('password')<p class="ar-error">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="ar-btn ar-btn--accent w-full" style="padding:.7rem 1rem;font-size:.9rem">
            Potvrdiť heslo
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ route('password.request') }}" class="ar-link font-semibold">Zabudnuté heslo?</a>
@endsection
