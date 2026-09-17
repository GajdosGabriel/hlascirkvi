@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    // Súkromná stránka prihláseného čitateľa — vo vyhľadávači nemá čo robiť.
    $seo = [
        'title' => 'Uložené príspevky',
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
            <h1 class="ar-display text-3xl font-extrabold leading-tight">Uložené na neskôr</h1>
            <p class="mt-2 text-sm text-gray-500">
                Príspevky, ktoré ste si odložili tlačidlom „Uložiť“. Zoznam vidíte len vy.
            </p>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        @if ($posts->isEmpty())
            <p class="rounded-lg border border-dashed border-[color:var(--ar-line)] bg-white px-4 py-10 text-center text-sm text-gray-500">
                Zatiaľ nemáte nič uložené. Pri videu alebo článku kliknite na
                <span class="whitespace-nowrap"><i class="far fa-bookmark"></i> Uložiť</span>.
            </p>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($posts as $post)
                    @include('posts.card-front')
                @endforeach
            </div>

            <div class="mt-8">
                {{ $posts->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection
