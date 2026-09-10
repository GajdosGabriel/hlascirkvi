@extends('layouts.app')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Vzdelávanie, konferencie a púte',
        'description' => 'Kresťanské konferencie, kurzy, semináre a púte na Slovensku '
            . 'spolu so záznamami prednášok.',
        'jsonld' => [
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                ['Vzdelávanie a kurzy', route('konferencie.pute')],
            ]),
        ],
    ];
@endphp

@section('body-class', 'ar-body')

@section('headerCSS')
<style>
    .seminars-page {
        max-width: 1152px;
        margin: 0 auto;
        padding: 44px 24px 72px;
    }
    .seminars-page a:focus-visible {
        outline: 3px solid var(--ar-accent);
        outline-offset: 4px;
        border-radius: 4px;
    }
    .seminars-hero {
        border-bottom: 1px solid var(--ar-line);
        padding-bottom: 32px;
        margin-bottom: 36px;
    }
    .seminars-hero-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
    }
    .seminars-hero h1 {
        font-size: clamp(2rem, 5vw, 3.25rem);
        font-weight: 800;
        line-height: 1.12;
        margin: 12px 0 16px;
    }
    .seminars-intro {
        max-width: 640px;
        color: var(--ar-ink-soft);
        line-height: 1.8;
    }
    .seminars-create {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        gap: 8px;
        margin-top: 12px;
        padding: 12px 18px;
        border-radius: 8px;
        background: var(--ar-accent);
        color: #fff;
        font-size: .875rem;
        font-weight: 600;
    }
    .seminars-create:hover { background: #991b1b; }
    .seminars-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 24px;
        margin-top: 24px;
        color: var(--ar-ink-soft);
        font-size: .8125rem;
    }
    .seminars-summary strong { color: var(--ar-ink); }
    .seminar-section {
        margin-bottom: 36px;
        padding-bottom: 36px;
        border-bottom: 1px solid var(--ar-line);
    }
    .seminar-section:last-child { border-bottom: 0; margin-bottom: 0; padding-bottom: 0; }
    .seminar-heading {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
    }
    .seminar-icon {
        display: grid;
        place-items: center;
        flex: 0 0 44px;
        height: 44px;
        border: 1px solid var(--ar-line);
        border-radius: 12px;
        background: #fff;
        color: var(--ar-accent);
    }
    .seminar-info { flex: 1; min-width: 0; }
    .seminar-info h2 {
        font-size: clamp(1.25rem, 3vw, 1.65rem);
        font-weight: 750;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }
    .seminar-info h2 a:hover, .seminar-organization a:hover { color: var(--ar-accent); }
    .seminar-organization {
        color: var(--ar-ink-soft);
        font-size: .8125rem;
        margin-top: 8px;
    }
    .seminar-description {
        max-width: 800px;
        margin-top: 12px;
        font-size: .9375rem;
        color: var(--ar-ink-soft);
        line-height: 1.75;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }
    .seminar-detail {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        color: var(--ar-accent);
        font-size: .8125rem;
        font-weight: 700;
        padding-top: 6px;
    }
    .seminar-detail:hover { text-decoration: underline; }
    .seminar-recordings {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
    }
    .seminars-empty {
        padding: 24px;
        border: 1px dashed var(--ar-line);
        border-radius: 12px;
        color: var(--ar-ink-soft);
        font-size: .875rem;
        line-height: 1.7;
    }
    @media (max-width: 900px) {
        .seminar-recordings { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .seminars-hero-row { flex-direction: column; gap: 4px; }
    }
    @media (max-width: 640px) {
        .seminars-page { padding: 28px 16px 48px; }
        .seminar-heading { flex-wrap: wrap; gap: 12px; }
        .seminar-detail { margin-left: 56px; }
        .seminar-recordings { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    }
    @media (max-width: 380px) {
        .seminar-recordings { grid-template-columns: minmax(0, 1fr); }
    }
</style>
@endsection

@section('content')
<div class="seminars-page">
    <header class="seminars-hero">
        <p class="ar-kicker">Priestor pre duchovný rast</p>
        <div class="seminars-hero-row">
            <div>
                <h1 class="ar-display">Konferencie a púte</h1>
                <p class="seminars-intro">Vzdelávanie, kurzy a stretnutia, ktoré prehlbujú vieru. Objavte záznamy prednášok a vráťte sa k myšlienkam, ktoré vás oslovili.</p>
            </div>
            @auth
                <a class="seminars-create" href="{{ route('profile.canals.seminars.create', auth()->user()->org_id) }}">
                    <i class="fas fa-plus" aria-hidden="true"></i> Nový seminár
                </a>
            @endauth
        </div>
        @if ($seminars->isNotEmpty())
            <div class="seminars-summary">
                <span>Podujatia v archíve <strong>{{ $seminars->count() }}</strong></span>
                <span>Záznamy prednášok <strong>{{ $seminars->sum(fn ($seminar) => $seminar->posts->count()) }}</strong></span>
            </div>
        @endif
    </header>

    @forelse ($seminars as $seminar)
        <section class="seminar-section" aria-labelledby="seminar-{{ $seminar->id }}">
            <header class="seminar-heading">
                <span class="seminar-icon" aria-hidden="true"><i class="fas fa-book-open"></i></span>
                <div class="seminar-info">
                    <h2 class="ar-display" id="seminar-{{ $seminar->id }}">
                        <a href="{{ route('seminars.show', $seminar->id) }}">{{ $seminar->title }}</a>
                    </h2>
                    <p class="seminar-organization">
                        Pridal:
                        <a href="{{ route('organizations.show', $seminar->organization->id) }}">{{ $seminar->organization->title }}</a>
                    </p>
                    @if ($seminar->description)
                        <p class="seminar-description">{{ $seminar->description }}</p>
                    @endif
                </div>
                <a class="seminar-detail" href="{{ route('seminars.show', $seminar->id) }}" aria-label="Zobraziť podujatie: {{ $seminar->title }}">
                    Zobraziť podujatie <span aria-hidden="true">→</span>
                </a>
            </header>
            @if ($seminar->posts->isNotEmpty())
                <div class="seminar-recordings">
                    @foreach ($seminar->posts as $post)
                        @include('posts.card-front')
                    @endforeach
                </div>
            @else
                <p class="seminars-empty">Záznamy z tohto podujatia zatiaľ nie sú k dispozícii.</p>
            @endif
        </section>
    @empty
        <p class="seminars-empty">Momentálne tu nie sú žiadne podujatia. Pozrite sa sem opäť neskôr.</p>
    @endforelse
</div>
@endsection
