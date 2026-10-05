@extends('layouts.legacy-page')

@php $seo = ['title' => 'Video']; @endphp

@section('content')
    <div class="ar-document">
        <header class="ar-document__header">
            <p class="ar-kicker">Videotéka</p>
            <h1 class="ar-display">Video</h1>
        </header>
        <div class="ar-document__card">
            <div class="ar-video-player">
                <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0" title="Video – Hlas Cirkvi" loading="lazy" allowfullscreen></iframe>
            </div>
        </div>
        <a href="{{ url('/') }}" class="ar-btn ar-btn--quiet mt-6">Prejsť na úvodnú stránku</a>
    </div>
@endsection
