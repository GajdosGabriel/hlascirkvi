@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">

                {{--<h2 class="page-header">Post show</h2>--}}

                    <div class="video-header">
{{--                                <h4>{{ $video->snippet->title }}</h4>--}}
{{--                                <span>Vysielané {{ date("d. M. Y", strtotime($video->snippet->publishedAt))  }}</span>--}}
                    </div>

                    <div class="video-container">
                        <iframe width="640" height="360" src="//www.youtube.com/embed/{{ $videoId }}?rel=0" frameborder="0" allowfullscreen></iframe>
                    </div>

            </div>


            <div class="page-aside">
                {{--<h4>user profil</h4>--}}

            </div>

        </div>
    </div>


    @endsection