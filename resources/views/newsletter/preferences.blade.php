@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    $seo = [
        'title' => 'Odber noviniek',
        'noindex' => true,
    ];
@endphp

@section('headerCSS')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
@endsection

@section('content')
    <header class="border-b border-[color:var(--ar-line)] bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8">
            <h1 class="ar-display text-3xl font-extrabold leading-tight">Odber noviniek</h1>
            <p class="mt-2 text-sm text-gray-500">
                Mesačný prehľad najsledovanejších videí a nových modlitieb.
                E-maily týkajúce sa účtu (napríklad obnova hesla) chodia ďalej.
            </p>
        </div>
    </header>

    <div class="mx-auto max-w-xl px-4 py-8">
        @if (session('flash'))
            <p class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('flash') }}</p>
        @endif

        <div class="rounded-lg border border-[color:var(--ar-line)] bg-white p-6">
            @if ($user->send_email)
                <p class="text-sm">Novinky dostávate na <strong>{{ $user->email }}</strong>.</p>
            @else
                <p class="text-sm">
                    Novinky vám neposielame{{ $user->newsletter_unsubscribed_at ? ' (odhlásené '.$user->newsletter_unsubscribed_at->format('j. n. Y').')' : '' }}.
                </p>
            @endif

            <form method="POST" action="{{ route('newsletter.preferences.update') }}" class="mt-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="send_email" value="{{ $user->send_email ? 0 : 1 }}">
                <button type="submit" class="ar-btn {{ $user->send_email ? 'ar-btn--quiet' : 'ar-btn--accent' }}">
                    {{ $user->send_email ? 'Zrušiť odber' : 'Zrušiť, chcem odber' }}
                </button>
            </form>
        </div>
    </div>
@endsection
