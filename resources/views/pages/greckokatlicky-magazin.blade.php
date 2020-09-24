@extends('layouts.app')

@section('content')
<div class="container">

    @include('pages.header-nav')

    <h2 class="page-header">Gréckokatolícky Magazín</h2>

    @foreach($greckokatolickyMgazins as $video)
        <div class="video-item">

            <div class="video-header">
                <h4>{{ $video->snippet->title }}</h4>
                <span>Vysielané {{ date("d. M. Y", strtotime($video->snippet->publishedAt))  }}</span>
            </div>

            <div class="video-container">
                <iframe width="640" height="360" src="//www.youtube.com/embed/{{ $video->contentDetails->upload->videoId }}?rel=0;modestbranding=1" frameborder="0" allowfullscreen></iframe>
            </div>

            <div class="video-item__side">
                <div>
                    <h5>Predchádzajúce prenosy</h5>
                    @foreach($greckokatolickyMgazins as $video)
                        <ul>
                            <a href="{{ route('post.showVideo', [$video->contentDetails->upload->videoId]) }}"><li style="font-size: 90%">{{ $video->snippet->title }}</li></a>
                        </ul>
                    @endforeach
                </div>

                <div style="margin-top: auto; font-size: 80%; cursor: pointer">
                    <messenger></messenger>
                </div>

            </div>
        </div>
    @endforeach


</div>
@endsection
