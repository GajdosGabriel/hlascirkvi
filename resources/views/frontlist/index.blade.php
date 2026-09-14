@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    $seo = [
        'title' => 'Kresťanské osobnosti a spoločenstvá',
        'description' => 'Kazatelia, kňazi, cirkvi a kresťanské spoločenstvá, ktorých kázne a videá '
            . 'nájdete na Hlase cirkvi. Pri každom je počet zverejnených príspevkov.',
        'canonical' => route('frontlist.index'),
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <header class="mb-6">
            <h1 class="text-2xl font-bold">Kresťanské osobnosti a spoločenstvá</h1>
            <p class="mt-1 text-gray-600">
                Kazatelia, kňazi, cirkvi a spoločenstvá, ktorých príspevky sledujeme. Číslo pri mene
                hovorí, koľko ich na webe už je.
            </p>
        </header>

        @forelse ($groups as $group)
            <section id="{{ $group['kind']->anchor() }}" class="mb-10 scroll-mt-24">
                <h2 class="mb-3 text-xl font-semibold">{{ $group['kind']->cardTitle() }}</h2>

                <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($group['canals'] as $canal)
                        <li>
                            <a href="{{ route('organizations.show', [$canal->id]) }}"
                               class="flex h-full items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 hover:border-gray-400">

                                {{-- Iniciály ležia pod obrázkom, nie vedľa neho: kanál si
                                     avatar nesie len ako meno súboru a ten na disku
                                     chýbať môže (onerror obrázok odstráni). --}}
                                <span class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-100 font-semibold text-gray-500">
                                    {{ $canal->initials() }}

                                    @if ($canal->avatarUrl())
                                        <img src="{{ $canal->avatarUrl() }}" alt="" loading="lazy" onerror="this.remove()"
                                             class="absolute inset-0 h-full w-full object-cover">
                                    @endif
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-semibold">{{ $canal->title }}</span>
                                    <span class="block text-sm text-gray-500">
                                        {{ $canal->postsCount }} {{ trans_choice('príspevok|príspevky|príspevkov', $canal->postsCount) }}
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="text-gray-500">Zoznam je zatiaľ prázdny.</p>
        @endforelse
    </div>
@endsection
