@extends('layouts.app')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Priame prenosy nedeľných bohoslužieb a svätých omší',
        'description' => 'Živé prenosy nedeľných bohoslužieb a svätých omší z kostolov a zborov '
            . 'na Slovensku. Sledujte online, keď sa nemôžete zúčastniť osobne.',
        'jsonld' => [
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                ['Priame prenosy', route('online-prenosy')],
            ]),
        ],
    ];
@endphp

@section('body-class', 'ar-body')
@section('headerCSS')
<style>
.streams {
        max-width:1152px;
        margin:auto;
        padding:44px 24px 72px
    }
    .streams a:focus-visible {
        outline:3px solid var(--ar-accent);
        outline-offset:4px
    }
    .streams-hero {
        border-bottom:1px solid var(--ar-line);
        padding-bottom:32px;
        margin-bottom:32px
    }
    .streams-hero h1 {
        font-size:clamp(2rem,5vw,3.25rem);
        font-weight:800;
        line-height:1.12;
        margin:12px 0 16px
    }
    .streams-intro {
        max-width:640px;
        color:var(--ar-ink-soft);
        line-height:1.8
    }
    .streams-nav {
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:24px
    }

.stream {
        background:#fff;
        border:1px solid var(--ar-line);
        border-radius:16px;
        margin-bottom:28px;
        scroll-margin-top:96px
    }
    .stream-heading {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:16px;
        padding:22px 26px;
        border-bottom:1px solid var(--ar-line)
    }
    .stream-channel {
        display:flex;
        align-items:center;
        gap:12px;
        min-width:0
    }
    .stream-icon {
        display:grid;
        place-items:center;
        flex:0 0 40px;
        height:40px;
        border-radius:12px;
        background:var(--ar-accent-soft);
        color:var(--ar-accent)
    }
    .stream-heading h2 {
        font-size:1.125rem;
        font-weight:700;
        overflow-wrap:anywhere
    }
    .stream-channel a:hover,.stream-title a:hover {
        color:var(--ar-accent)
    }

.stream-grid {
        display:grid;
        grid-template-columns:minmax(0,1.9fr) minmax(0,1fr)
    }
    .stream-feature {
        min-width:0;
        padding:24px
    }
    .stream-player {
        aspect-ratio:16/9;
        overflow:hidden;
        border-radius:10px;
        background:var(--ar-paper-deep)
    }
    .stream-player>a {
        display:block;
        height:100%
    }
    .stream-player iframe,.stream-player img {
        display:block;
        width:100%;
        height:100%;
        border:0;
        object-fit:cover
    }
    .stream-meta {
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        gap:12px;
        margin:20px 0 10px;
        font-size:.75rem;
        color:var(--ar-ink-soft)
    }
    .stream-label {
        color:var(--ar-accent);
        font-weight:700;
        background:var(--ar-accent-soft);
        border-radius:4px;
        padding:4px 8px
    }
    .stream-title {
        font-size:1.25rem;
        font-weight:700;
        line-height:1.45;
        overflow-wrap:anywhere
    }

.stream-archive {
        min-width:0;
        border-left:1px solid var(--ar-line);
        padding:26px 24px
    }
    .stream-archive h3 {
        font-size:.75rem;
        letter-spacing:.09em;
        text-transform:uppercase;
        font-weight:700;
        color:var(--ar-ink-soft);
        margin-bottom:12px
    }
    .stream-archive li+li {
        border-top:1px solid var(--ar-line)
    }
    .stream-archive-link {
        display:flex;
        align-items:flex-start;
        gap:12px;
        padding:17px 0
    }
    .stream-play {
        flex:0 0 30px;
        height:30px;
        display:grid;
        place-items:center;
        border-radius:50%;
        background:var(--ar-paper);
        color:var(--ar-ink-soft);
        font-size:.6rem
    }
    .stream-archive-link:hover {
        color:var(--ar-accent)
    }
    .stream-archive-link:hover .stream-play {
        background:var(--ar-accent-soft);
        color:var(--ar-accent)
    }
    .stream-archive-title {
        display:block;
        font-size:.875rem;
        line-height:1.5;
        font-weight:600;
        overflow-wrap:anywhere
    }
    .stream-date {
        display:block;
        font-size:.75rem;
        color:var(--ar-ink-soft);
        margin-top:6px
    }
    .stream-all {
        display:inline-flex;
        align-items:center;
        gap:8px;
        font-size:.8125rem;
        font-weight:700;
        color:var(--ar-accent);
        margin-top:18px
    }
    .stream-all:hover {
        text-decoration:underline
    }
    .streams-empty {
        padding:40px 24px;
        border:1px dashed var(--ar-line);
        border-radius:12px;
        color:var(--ar-ink-soft);
        line-height:1.7
    }

@media(max-width:767px) {
        .streams {
        padding:28px 16px 48px
    }
    .stream-grid {
        grid-template-columns:minmax(0,1fr)
    }
    .stream-heading,.stream-feature {
        padding:18px
    }
    .stream-archive {
        border-left:0;
        border-top:1px solid var(--ar-line);
        padding:22px 18px
    }

    }
</style>
@endsection

@section('content')
<div class="streams">
    <header class="streams-hero">
        <p class="ar-kicker">Viera nás spája</p>
        <h1 class="ar-display">Nedeľné bohoslužby</h1>
        <p class="streams-intro">Priame prenosy a záznamy bohoslužieb a svätých omší z kostolov a zborov na Slovensku. Buďte súčasťou spoločenstva aj vtedy, keď sa nemôžete zúčastniť osobne.</p>
        @if ($posts->count() > 1)
            <nav class="streams-nav" aria-label="Vybrať spoločenstvo">
                @foreach ($posts as $organizationId => $broadcasts)
                    <a class="ar-tab" href="#spolocenstvo-{{ $organizationId }}">{{ $broadcasts->first()->organization->title }}</a>
                @endforeach
            </nav>
        @endif
    </header>
    @forelse ($posts as $organizationId => $broadcasts)
        @php($post = $broadcasts->first())
        <section class="stream" id="spolocenstvo-{{ $organizationId }}" aria-labelledby="kanal-{{ $organizationId }}">
            <header class="stream-heading">
                <div class="stream-channel">
                    <span class="stream-icon" aria-hidden="true"><i class="fas fa-church"></i></span>
                    <h2 id="kanal-{{ $organizationId }}"><a href="{{ route('organizations.show', [$post->organization->id]) }}">{{ $post->organization->title }}</a></h2>
                </div>
                @can('update', $post)
                    <article-dropdown :post="{{ $post }}"></article-dropdown>
                @endcan
            </header>
            <div class="stream-grid">
                <div class="stream-feature">
                    <div class="stream-player">
                        @if ($post->video_id)
                            <iframe src="https://www.youtube.com/embed/{{ $post->video_id }}?rel=0" title="{{ $post->title }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        @else
                            <a href="{{ $post->routeShow() }}"><img src="{{ url($post->thumb_image) }}" alt="{{ $post->title }}" loading="lazy"></a>
                        @endif
                    </div>
                    <div class="stream-meta">
                        <span class="stream-label">Najnovší prenos</span>
                        <time datetime="{{ $post->created_at->format('Y-m-d') }}">{{ $post->created_at->format('d. m. Y') }}</time>
                    </div>
                    <h3 class="stream-title ar-display"><a href="{{ $post->routeShow() }}">{{ $post->title }}</a></h3>
                </div>
                <aside class="stream-archive" aria-label="Archív prenosov – {{ $post->organization->title }}">
                    <h3>Predchádzajúce prenosy</h3>
                    <ul>
                        @forelse ($broadcasts->skip(1)->take(4) as $previousPost)
                            <li>
                                <a class="stream-archive-link" href="{{ $previousPost->routeShow() }}">
                                    <span class="stream-play" aria-hidden="true"><i class="fas fa-play"></i></span>
                                    <span>
                                        <span class="stream-archive-title">{{ $previousPost->title }}</span>
                                        <time class="stream-date" datetime="{{ $previousPost->created_at->format('Y-m-d') }}">{{ $previousPost->created_at->format('d. m. Y') }}</time>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="stream-date">Ďalšie záznamy pribudnú po odvysielaní.</li>
                        @endforelse
                    </ul>
                    <a class="stream-all" href="{{ route('organizations.show', [$post->organization->id]) }}">Všetky príspevky spoločenstva <span aria-hidden="true">→</span></a>
                </aside>
            </div>
        </section>
    @empty
        <div class="streams-empty">Momentálne tu nie sú žiadne prenosy. Pozrite sa sem opäť neskôr.</div>
    @endforelse
</div>
@endsection
