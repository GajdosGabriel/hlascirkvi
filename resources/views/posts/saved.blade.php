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
                @if ($posts->total() > 0)
                    <span class="font-semibold text-gray-700">Uložených: {{ $posts->total() }}</span>
                @endif
            </p>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        @if ($posts->isEmpty())
            <p class="rounded-lg border border-dashed border-[color:var(--ar-line)] bg-white px-4 py-10 text-center text-sm text-gray-500">
                Zatiaľ nemáte nič uložené. Pri videu alebo článku kliknite na
                <span class="whitespace-nowrap"><i class="ph ph-bookmark-simple"></i> Uložiť</span>.
            </p>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($posts as $post)
                    <div class="flex flex-col gap-2">
                        <div class="flex-1">@include('posts.card-front')</div>
                        <form method="POST" action="{{ route('saved.destroy', $post->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ar-btn ar-btn--quiet w-full">
                                <i class="ph ph-bookmark-simple-slash"></i> Odstrániť
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $posts->onEachSide(1)->links() }}
            </div>
        @endif

        <p class="mt-8 text-center text-xs text-gray-500">
            <a href="{{ route('newsletter.preferences') }}" class="ar-link">Nastavenie odberu noviniek</a>
        </p>
    </div>
@endsection
