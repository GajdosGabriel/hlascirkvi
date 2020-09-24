@extends('layouts.app')

@section('content')
<div class="container">

    @include('pages.header-nav')

    <h2 class="page-header">Hanusové dni 2018</h2>

    @foreach($hanusoveDnis as $video)
        <div class="video-item">

            <div class="video-header">
                <h4>{{ $video->snippet->title }}</h4>
                <span>{{ date("d. M. Y", strtotime($video->snippet->publishedAt))  }}</span>
            </div>

            <div class="video-container">
                <iframe width="640" height="360" src="//www.youtube.com/embed/{{ $video->snippet->resourceId->videoId }}?rel=0;modestbranding=1" frameborder="0" allowfullscreen></iframe>
            </div>

            <div class="video-item__side">
                {{--<div>--}}
                    {{--<h5>Predchádzajúce prenosy</h5>--}}
                    {{--@foreach($milostBystricas as $video)--}}
                    {{--<span style="font-size: 80%">{{ $video->snippet->title }}</span><br>--}}
                    {{--@endforeach--}}
                {{--</div>--}}

                <div style="margin-top: auto; font-size: 80%; cursor: pointer">
                    <messenger></messenger>
                </div>

            </div>
        </div>
    @endforeach


</div>
@endsection
