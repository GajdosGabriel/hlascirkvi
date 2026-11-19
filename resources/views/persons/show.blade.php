@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    /*
     * Stránka osoby. Hlavička nesie meno, počet videí, vlastný kanál a zbory,
     * v ktorých osoba pôsobí; pod ňou výpis videí, v ktorých vystupuje.
     */
    $plural = function ($count, $one, $few, $many) {
        if ($count === 1) return $one;
        return $count >= 2 && $count <= 4 ? $few : $many;
    };

    $page = $posts->currentPage();
    $personUrl = route('person.show', $person);
    $pageUrl = fn ($n) => $n > 1 ? $personUrl . '?page=' . $n : $personUrl;

    $where = $canals->pluck('title')->take(3)->implode(', ');
    $description = 'Kázne a videá, v ktorých vystupuje ' . $person->name
        . ($where !== '' ? ' (' . $where . ')' : '') . ', na Hlase Cirkvi.';

    $seo = [
        'title' => $page > 1 ? $person->name . ' – strana ' . $page : $person->name . ' – kázne a videá',
        'description' => $description,
        'canonical' => $pageUrl($page),
        // Osoba bez jediného videa je len prázdna stránka.
        'noindex' => $posts->total() === 0 ?: null,
        'prev' => $page > 1 ? $pageUrl($page - 1) : null,
        'next' => $posts->hasMorePages() ? $pageUrl($page + 1) : null,
        'jsonld' => [
            array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $person->name,
                'url' => $personUrl,
                'affiliation' => $canals->map(fn ($canal) => [
                    '@type' => 'Organization',
                    'name' => $canal->title,
                    'url' => route('organizations.show', [$canal->id]),
                ])->values()->all() ?: null,
            ]),
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                [$person->name, $personUrl],
            ]),
        ],
    ];
@endphp

@section('headerCSS')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
@endsection

@section('content')

    <header class="border-b border-(--ar-line) bg-white">
        <div class="mx-auto max-w-6xl px-4">

            <div class="py-3 text-sm text-gray-500">
                <a href="{{ url('/') }}" class="hover:text-gray-900">Hlas Cirkvi</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="text-gray-700">{{ $person->name }}</span>
            </div>

            <div class="flex items-center gap-4 border-t border-(--ar-line) py-6 md:gap-6 md:py-8">
                @php $person->setRelation('canal', $ownCanal); @endphp
                @include('persons._avatar', ['size' => 'h-16 w-16 text-xl md:h-20 md:w-20 md:text-2xl'])

                <div class="min-w-0">
                    <h1 class="ar-display text-2xl font-extrabold leading-tight md:text-[2.1rem]">
                        {{ $person->name }}
                        @if (auth()->user()?->hasRole('superadmin'))
                            <a href="{{ route('admin.persons.edit', $person) }}" class="align-middle text-sm font-normal text-gray-400 hover:text-(--ar-accent)"
                               title="Upraviť v administrácii"><i class="ph ph-pencil-simple-line"></i></a>
                        @endif
                    </h1>
                    @if ($person->description)
                        <p class="mt-1 text-gray-600">{{ $person->description }}</p>
                    @endif
                    <p class="mt-1 text-sm text-gray-500">
                        {{ number_format($posts->total(), 0, ',', ' ') }}
                        {{ $plural($posts->total(), 'video', 'videá', 'videí') }} na Hlase Cirkvi
                    </p>
                </div>
            </div>

            @if ($ownCanal || $canals->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 pb-6">
                    @if ($ownCanal)
                        <a href="{{ route('organizations.show', [$ownCanal->id]) }}" class="ar-tab ar-tab--on">
                            <i class="ph ph-user-circle"></i> Vlastný kanál
                        </a>
                    @endif

                    @if ($canals->isNotEmpty())
                        <span class="ml-1 text-sm text-gray-500">Pôsobí v:</span>
                        @foreach ($canals as $canal)
                            <a href="{{ route('organizations.show', [$canal->id]) }}" class="ar-tab">
                                <i class="ph ph-church"></i> {{ $canal->title }}@if ($canal->pivot->role)<span class="text-gray-500">&nbsp;· {{ $canal->pivot->role }}</span>@endif
                            </a>
                        @endforeach
                    @endif
                </div>
            @endif
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            @forelse ($posts as $post)
                @include('posts.card-front')
            @empty
                <p class="col-span-full rounded-lg border border-dashed border-(--ar-line) bg-white px-4 py-10 text-center text-sm text-gray-500">
                    Zatiaľ tu nie je žiadne zverejnené video.
                </p>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $posts->onEachSide(1)->links() }}
        </div>
    </div>
@endsection
