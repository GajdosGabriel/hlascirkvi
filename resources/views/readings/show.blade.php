@extends('layouts.app')

@section('body-class', 'ar-body')

@php
    $color     = $day->color();
    $dateLabel = $day->calendar->date->translatedFormat('l, j. F Y');
    $canonical = $isToday ? route('readings.show') : route('readings.show', [$day->calendar->date->format('Y-m-d')]);
    $gospel    = $day->record?->gospel();

    $seo = [
        'title' => ($isToday ? 'Liturgické čítania na dnes' : 'Liturgické čítania') . ' – ' . $day->title() . ', ' . $dateLabel,
        'description' => trim(implode(' · ', $day->calendar->contextParts())
            . ($gospel ? '. Evanjelium: ' . $gospel['citation'] . ($gospel['heading'] ? ' – ' . $gospel['heading'] : '') : '')),
        'canonical' => $canonical,
        'jsonld' => [
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                ['Liturgické čítania', route('readings.show')],
            ]),
        ],
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-8">

        {{-- Týždeň okolo dňa. Bodka nesie liturgickú farbu dňa podľa kalendára. --}}
        <nav class="mb-6 flex items-center gap-2 text-sm" aria-label="Iné dni">
            @if ($prev)
                <a href="{{ route('readings.show', [$prev->format('Y-m-d')]) }}" class="rounded px-2 py-1 hover:bg-gray-100" title="Predchádzajúci deň">
                    <i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">Predchádzajúci deň</span>
                </a>
            @endif

            <ol class="grid flex-1 grid-cols-7 gap-1">
                @foreach ($week as $other)
                    @php $current = $other->date->isSameDay($day->calendar->date); @endphp
                    <li>
                        <a href="{{ route('readings.show', [$other->date->format('Y-m-d')]) }}"
                           @if ($current) aria-current="date" @endif
                           title="{{ $other->title }}"
                           class="flex flex-col items-center rounded py-1 {{ $current ? 'bg-gray-800 text-white' : 'hover:bg-gray-100' }} {{ $other->isSunday() && ! $current ? 'font-bold' : '' }}">
                            <span class="text-[0.7rem] uppercase">{{ $other->date->translatedFormat('D') }}</span>
                            <span>{{ $other->date->day }}.</span>
                            <span class="mt-0.5 h-1.5 w-1.5 rounded-full" style="background: {{ $other->color->hex() }}"></span>
                        </a>
                    </li>
                @endforeach
            </ol>

            @if ($next)
                <a href="{{ route('readings.show', [$next->format('Y-m-d')]) }}" class="rounded px-2 py-1 hover:bg-gray-100" title="Nasledujúci deň">
                    <i class="fa fa-angle-right" aria-hidden="true"></i><span class="sr-only">Nasledujúci deň</span>
                </a>
            @endif
        </nav>

        <header class="border-l-4 pl-4" style="border-color: {{ $color->hex() }}">
            <p class="text-sm uppercase tracking-wide text-gray-500">
                {{ $dateLabel }}
                @unless ($isToday)
                    · <a href="{{ route('readings.show') }}">prejsť na dnešok</a>
                @endunless
            </p>
            <h1 class="mt-1 text-2xl font-bold leading-tight md:text-3xl">{{ $day->title() }}</h1>

            @if ($day->subtitle())
                <p class="mt-1 text-gray-600">{{ $day->subtitle() }}</p>
            @endif

            <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-2 py-0.5">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $color->hex() }}"></span>
                    {{ ucfirst($color->label()) }}
                </span>
                @if ($day->obligation())
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-800">Prikázaný sviatok</span>
                @endif
                <span>{{ implode(' · ', $day->calendar->contextParts()) }}</span>
            </p>
        </header>

        @forelse ($day->sections() as $section)
            <section class="mt-8">
                @if ($section['title'])
                    <h2 class="mb-3 text-lg font-semibold text-gray-700">{{ $section['title'] }}</h2>
                @endif

                <div class="space-y-5">
                    @foreach ($section['lines'] as $line)
                        <article class="rounded-lg border {{ $line['type'] === 'gospel' ? 'border-gray-300 bg-gray-50' : 'border-gray-200 bg-white' }} p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $line['label'] }}</p>

                            @if ($line['type'] === 'gospel' && $line['acclamation'])
                                <p class="mt-1 text-sm italic text-gray-600">Aleluja. {{ $line['acclamation'] }}</p>
                            @endif

                            @foreach ($line['options'] as $option)
                                @unless ($loop->first)
                                    <p class="my-3 text-sm font-semibold text-gray-500">{{ $option['label'] ?: 'alebo' }}</p>
                                @endunless

                                <h3 id="{{ $option['anchor'] }}" class="mt-1 scroll-mt-24 text-lg font-bold text-gray-800">
                                    @if ($option['bible_url'])
                                        <a href="{{ $option['bible_url'] }}" target="_blank" rel="noopener" title="Kontext v Biblii">{{ $option['citation'] }}</a>
                                    @else
                                        {{ $option['citation'] }}
                                    @endif
                                </h3>

                                @if ($option['intro'] && $option['intro'] !== $line['label'])
                                    <p class="text-sm text-gray-500">{{ $option['intro'] }}</p>
                                @endif

                                @if ($option['heading'])
                                    <p class="mt-1 font-semibold italic text-gray-700">„{{ $option['heading'] }}“</p>
                                @endif

                                @if ($fullTexts && $option['text'])
                                    <div class="mt-3 space-y-3 whitespace-pre-line leading-relaxed text-gray-800">{{ $option['text'] }}</div>
                                @endif
                            @endforeach

                            @if ($line['type'] === 'psalm' && $line['response'])
                                <p class="mt-2 text-gray-700"><strong>R.:</strong> {{ $line['response'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="mt-8 rounded-lg border border-dashed border-gray-300 bg-white px-4 py-8 text-center text-gray-600">
                Čítania na tento deň sa teraz nepodarilo načítať.
                <a href="{{ $day->sourceUrl }}" target="_blank" rel="noopener">Pozrite si ich v liturgickom kalendári KBS.</a>
            </p>
        @endforelse

        @if ($homilies->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-lg font-semibold">Toto evanjelium v archíve</h2>
                <p class="text-sm text-gray-500">
                    Nedeľné čítania sa opakujú každé tri roky. Toto sme zverejnili, keď sa čítali naposledy.
                </p>

                <ul class="mt-3 divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
                    @foreach ($homilies as $homily)
                        <li>
                            <a href="{{ route('post.show', [$homily['id'], $homily['slug']]) }}" class="block px-4 py-3 hover:bg-gray-50">
                                <span class="block font-semibold">{{ $homily['title'] }}</span>
                                <span class="text-sm text-gray-500">
                                    {{ $homily['canal'] }} · {{ \Carbon\Carbon::parse($homily['published_at'])->translatedFormat('j. F Y') }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-10 space-y-1 border-t border-gray-200 pt-4 text-sm text-gray-500">
            <p>
                Do {{ $milestone['until'] }} ešte {{ $milestone['days_label'] }}
                ({{ $milestone['date']->translatedFormat('j. F Y') }}).
            </p>
            <p>
                Citácie a názvy dní preberáme z
                <a href="{{ $day->sourceUrl }}" target="_blank" rel="noopener">liturgického kalendára KBS</a>,
                kde je aj plné znenie čítaní.
            </p>
        </footer>
    </div>
@endsection
