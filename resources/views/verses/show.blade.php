@extends('layouts.legacy-page')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. Popis
       skladá samotné zamyslenie, pri ňom stojí biblický verš — to je to,
       čo z tejto stránky vidno vo vyhľadávaní. */
    $seo = [
        'title' => $post->title . ' – zamyslenie na ' . $date,
        'description' => trim($post->biblicky_vers . ' ' . strip_tags((string) $post->zamyslenie)),
        'canonical' => route('verses.index', [$post->slug]),
        'type' => 'article',
        'author' => $post->autor ?: null,
        'section' => 'Zamyslenia',
        'jsonld' => [
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                ['Zamyslenia', route('verses.index')],
                [$post->title, route('verses.index', [$post->slug])],
            ]),
        ],
    ];
@endphp

@section('content')
    <div class="ar-document">
        <header class="ar-document__header">
            <p class="ar-kicker">Denné zamyslenie</p>
            <h1 class="ar-display">{{ $post->title }}</h1>
            <p class="ar-document__intro">Zamyslenie na {{ $date }}</p>
        </header>
        <div class="grid gap-6 lg:grid-cols-3">
            <article class="ar-document__card lg:col-span-2">
                <div class="ar-document__prose space-y-5">
                    {!! $post->zamyslenie !!}
                    @if ($post->autor)
                        <p class="border-t border-[color:var(--ar-line)] pt-5 text-sm">{{ $post->autor }}</p>
                    @endif
                </div>
                <nav class="mt-8 flex flex-wrap justify-between gap-3 border-t border-[color:var(--ar-line)] pt-6" aria-label="Ďalšie zamyslenia">
                    @if ($previous)
                        <a class="ar-btn ar-btn--quiet" href="{{ URL::to('zamyslenia/' . $previous) }}">← Predchádzajúce</a>
                    @endif
                    @if ($next)
                        <a class="ar-btn ar-btn--quiet" href="{{ URL::to('zamyslenia/' . $next) }}">Nasledujúce →</a>
                    @endif
                </nav>
            </article>
            <aside class="space-y-6" aria-label="Biblické verše">
                <section class="ar-document__card">
                    <h2 class="ar-kicker">Biblický verš k zamysleniu</h2>
                    <blockquote class="mt-4 text-lg leading-relaxed">
                        <p>{{ $post->biblicky_vers }}</p>
                        <footer class="mt-4 text-sm text-[color:var(--ar-ink-soft)]">{{ $post->biblicky_vers_ref }}</footer>
                    </blockquote>
                </section>
                <section class="ar-document__card">
                    <h2 class="ar-kicker">Verš starej zmluvy</h2>
                    <blockquote class="mt-4 text-lg leading-relaxed">
                        <p>{{ $post->szvers_text }}</p>
                        <footer class="mt-4 text-sm text-[color:var(--ar-ink-soft)]">{{ $post->szvers_ref }}</footer>
                    </blockquote>
                </section>
                <img class="w-full rounded-xl" src="{{ asset('images/biblia1.jpg') }}" alt="Biblia" loading="lazy">
            </aside>
        </div>
    </div>
@endsection
