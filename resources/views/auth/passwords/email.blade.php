@extends('layouts.auth')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Zabudnuté heslo',
        'description' => 'Pošleme vám odkaz na nastavenie nového hesla k účtu na portáli Hlas Cirkvi.',
        'noindex' => true,
    ];
@endphp

@section('heading', 'Zabudnuté heslo')
@section('subtitle', 'Zadajte adresu, ktorou ste sa registrovali. Pošleme na ňu odkaz na nastavenie nového hesla.')

@section('card')
    @if (session('status'))
        <div class="ar-note ar-note--ok mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label class="ar-label" for="email">E-mailová adresa</label>
            <input id="email" name="email" type="email" autocomplete="email" inputmode="email"
                   value="{{ old('email') }}" maxlength="100" required autofocus
                   class="ar-input @error('email') ar-input--bad @enderror">
            @error('email')
                <p class="ar-error">{{ $message }}</p>
            @else
                <p class="ar-hint">Odkaz platí hodinu. Ak e-mail nepríde, pozrite priečinok s nevyžiadanou poštou.</p>
            @enderror
        </div>

        <button type="submit" class="ar-btn ar-btn--accent w-full" style="padding:.7rem 1rem;font-size:.9rem">
            Poslať odkaz
        </button>
    </form>
@endsection

@section('footer')
    Spomenuli ste si? <a href="{{ route('login') }}" class="ar-link font-semibold">Prihláste sa</a>
@endsection
