@extends('layouts.legacy-page')

@php $seo = ['title' => 'Hanusove dni 2018']; @endphp

@section('content')
    <div class="ar-document">
        <header class="ar-document__header">
            <p class="ar-kicker">Videoarchív</p>
            <h1 class="ar-display">Hanusove dni 2018</h1>
            <p class="ar-document__intro">Pozrite si záznamy a vyberte si video, ktoré vás zaujíma.</p>
        </header>
        <div class="ar-video-grid">
            @forelse ($hanusoveDnis as $video)
                <article class="ar-video-card">
                    <div class="ar-video-player">
                        <iframe src="https://www.youtube-nocookie.com/embed/{{ $video->snippet->resourceId->videoId }}?rel=0" title="{{ $video->snippet->title }}" loading="lazy" allowfullscreen></iframe>
                    </div>
                    <div class="ar-video-card__content">
                        <h2 class="ar-display">{{ $video->snippet->title }}</h2>
                        <time datetime="{{ $video->snippet->publishedAt }}">{{ date('d. m. Y', strtotime($video->snippet->publishedAt)) }}</time>
                    </div>
                </article>
            @empty
                <p class="ar-document__card">Momentálne tu nie sú dostupné žiadne videá.</p>
            @endforelse
        </div>
    </div>
@endsection
